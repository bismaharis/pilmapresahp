<?php

namespace App\Http\Controllers\Juri;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Criteria;
use App\Services\AssessmentService;
use App\Services\AhpCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentService $assessmentService,
        protected AhpCalculatorService $ahpCalculator
    ) {}

    public function index()
    {
        $user = Auth::user();
        $juri = $user->lecturer;

        // Mulai Query Dasar
        $query = Registration::with(['student.user', 'achievements'])
            ->whereIn('status', ['submitted', 'verified', 'approved']) 
            ->whereNotNull('file_gk')
            ->whereNotNull('file_transkrip'); 
            
        // PENERAPAN LOGIKA JURI
        if ($juri->is_univ_judge) {
            // Logika 5: Juri Universitas hanya melihat peserta tahap Universitas
            $query->where('stage', 'universitas');
        } else {
            // Logika 1 & 2: Juri Fakultas HANYA melihat peserta di fakultasnya pada tahap Fakultas
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            })->where('stage', 'fakultas');
        }

        // Eksekusi Query (Tanpa ditimpa ulang)
        $registrations = $query->get();

        // Kirim $juri ke view agar kita bisa mematikan tombol 'Nilai' untuk mhs satu prodi
        return view('juri.assessment.index', compact('registrations', 'juri'));
    }

    public function edit(Registration $registration)
    {
        $user = Auth::user();
        $juri = $user->lecturer;

        // PROTEKSI KEAMANAN BERLAPIS (Mencegah bypass URL)
        if ($juri->is_univ_judge) {
            // Juri Univ mencoba menilai peserta yang masih tahap fakultas
            if ($registration->stage != 'universitas') {
                abort(403, 'Akses Ditolak: Anda hanya dapat menilai peserta di tahap Universitas.');
            }
        } else {
            // Juri Fakultas mencoba menilai mhs dari fakultas lain
            if ($registration->student->faculty_id != $user->faculty_id) {
                abort(403, 'Akses Ditolak: Anda tidak berhak menilai mahasiswa dari Fakultas lain.');
            }
            // Logika 3 (Conflict of Interest): Juri Fakultas mencoba menilai mhs satu prodi (unit kerja)
            if ($registration->student->prodi == $juri->unit_kerja) {
                abort(403, 'Conflict of Interest: Anda dilarang menilai mahasiswa dari Program Studi Anda sendiri.');
            }
        }

        $registration->load('achievements');
        
        $criteriaTree = Criteria::whereNull('parent_id')
            // ->whereIn('type', ['gk', 'bi'])
            ->with(['children.children.children']) 
            ->get();

        $existingScores = \App\Models\Assessment::where('registration_id', $registration->id)
            ->where('lecturer_id', $juri->id)
            ->pluck('score', 'criteria_id')
            ->toArray();

        return view('juri.assessment.edit', compact('registration', 'criteriaTree', 'existingScores'));
    }

    public function update(Request $request, Registration $registration)
    {
        $request->validate([
            'scores' => 'nullable|array', // Diubah jadi nullable karena CU pakai array berbeda
            'scores.*' => 'numeric|min:0',
            'achievement_scores' => 'nullable|array', 
            'achievement_scores.*' => 'numeric|min:0|max:50',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string'
        ]);

        $juriId = Auth::user()->lecturer->id;

        try {
            $this->assessmentService->saveScores($registration->id, $juriId, $request->scores);
            $this->ahpCalculator->calculateFinalScore($registration);
            return redirect()->route('juri.assessments.index')->with('success', 'Nilai berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}