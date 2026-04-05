<?php

namespace App\Http\Controllers\Juri;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Criteria;
use App\Models\Registration;
use App\Services\AhpCalculatorService;
use App\Services\AssessmentService;
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

        // Base query untuk registration yang eligible dinilai
        $baseQuery = Registration::where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereIn('status', ['submitted', 'verified', 'approved'])
                    ->whereNotNull('file_gk')
                    ->whereNotNull('file_transkrip');
            })->orWhere(function ($sub) {
                $sub->where('status', 'draft')
                    ->whereNotNull('file_gk')
                    ->whereNotNull('file_transkrip');
            });
        });

        // Filter berdasarkan jenis juri
        if ($juri->is_univ_judge) {
            // Juri universitas hanya lihat mahasiswa yang sudah lolos ke universitas
            $query = $baseQuery->where('stage', 'universitas');
        } else {
            // Juri fakultas hanya lihat mahasiswa dari fakultasnya sendiri di tingkat fakultas
            $query = $baseQuery->where('stage', 'fakultas')
                ->whereHas('student', function ($q) use ($juri) {
                    $q->where('faculty_id', $juri->faculty_id);
                });
        }

        // Eager load relationships AFTER filtering
        $registrations = $query->with(['student.user', 'achievements'])->get();

        $sudahDinilai = Assessment::where('lecturer_id', $juri->id)
            ->whereIn('registration_id', $registrations->pluck('id'))
            ->pluck('registration_id')
            ->unique()
            ->toArray();

        return view('juri.assessment.index', compact('registrations', 'juri', 'sudahDinilai'));
    }

    public function edit(Registration $registration)
    {
        $user = Auth::user();
        $juri = $user->lecturer;
        $registration = Registration::with('achievements', 'student.user')->findOrFail($registration->id);

        // $existingAssessments = Assessment::where('registration_id', $registration->id)
        //     ->where('lecturer_id', auth()->id())
        //     ->get()
        //     ->keyBy('criterion_id');

        $this->authorizeJuri($juri, $registration);

        $registration->load('achievements');

        $criteriaTree = Criteria::whereNull('parent_id')
            ->with(['children.children.children'])
            ->get();

        $existingScores = Assessment::where('registration_id', $registration->id)
            ->where('lecturer_id', $juri->id)
            ->pluck('score', 'criteria_id')
            ->toArray();

        $existingNotes = Assessment::where('registration_id', $registration->id)
            ->where('lecturer_id', $juri->id)
            ->pluck('notes', 'criteria_id')
            ->toArray();

        return view('juri.assessment.edit', compact('registration', 'criteriaTree', 'existingScores', 'existingNotes'));
    }

    public function update(Request $request, Registration $registration)
    {
        $juri = Auth::user()->lecturer;

        $this->authorizeJuri($juri, $registration);

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0',
            'achievement_scores' => 'nullable|array',
            'achievement_scores.*' => 'numeric|min:0|max:50',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string',
        ]);

        // $juri->id = Auth::user()->lecturer->id;

        try {
            $this->assessmentService->saveScores(
                $registration->id,
                $juri->id,
                $request->scores ?? [],
                $request->notes ?? [],
                $request->achievement_scores ?? []
            );

            $this->ahpCalculator->calculateFinalScore($registration);

            // Pastikan registrasi ada di ranking/transparansi setelah dinilai.
            if (in_array($registration->status, ['draft', 'submitted'])) {
                $registration->update(['status' => 'verified']);
            }

            // Catatan: calculateFinalScore() sudah menyimpan nilai ke total_score_fakultas
            // atau total_score_univ berdasarkan tahap registrasi, jadi tidak perlu
            // update tambahan di sini.

            return redirect()->route('juri.assessments.index')->with('success', 'Nilai berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function authorizeJuri($juri, Registration $registration): void
    {
        if ($juri->is_univ_judge) {
            if ($registration->stage !== 'universitas') {
                abort(403, 'Akses Ditolak: Anda hanya dapat menilai peserta di tahap Universitas.');
            }
        } else {
            if ($registration->stage !== 'fakultas') {
                abort(403, 'Akses Ditolak: Anda hanya dapat menilai peserta di tahap Fakultas.');
            }
            if ($registration->student->faculty_id != $juri->faculty_id) {
                abort(403, 'Akses Ditolak: Anda tidak berhak menilai mahasiswa dari Fakultas lain.');
            }
            if ($registration->student->prodi == $juri->unit_kerja) {
                abort(403, 'Conflict of Interest: Anda dilarang menilai mahasiswa dari Program Studi Anda sendiri.');
            }
        }
    }
}
