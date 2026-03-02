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

        $query = Registration::with(['student.user', 'achievements'])
            ->whereIn('status', ['submitted', 'verified', 'approved'])
            ->whereNotNull('file_gk')
            ->whereNotNull('file_transkrip');

        // logika juri
        if ($juri->is_univ_judge) {
            $query->where('stage', 'universitas');
        } else {

            $query->whereHas('student', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            })->where('stage', 'fakultas');
        }

        $registrations = $query->get();

        return view('juri.assessment.index', compact('registrations', 'juri'));
    }

    public function edit(Registration $registration)
    {
        $user = Auth::user();
        $juri = $user->lecturer;
        $registration = Registration::with('achievements', 'student.user')->findOrFail($registration->id);

        if ($juri->is_univ_judge) {
            if ($registration->stage != 'universitas') {
                abort(403, 'Akses Ditolak: Anda hanya dapat menilai peserta di tahap Universitas.');
            }
        } else {
            if ($registration->student->faculty_id != $user->faculty_id) {
                abort(403, 'Akses Ditolak: Anda tidak berhak menilai mahasiswa dari Fakultas lain.');
            }
            if ($registration->student->prodi == $juri->unit_kerja) {
                abort(403, 'Conflict of Interest: Anda dilarang menilai mahasiswa dari Program Studi Anda sendiri.');
            }
        }

        // $existingAssessments = Assessment::where('registration_id', $registration->id)
        //     ->where('lecturer_id', auth()->id())
        //     ->get()
        //     ->keyBy('criterion_id');

        $registration->load('achievements');

        $criteriaTree = Criteria::whereNull('parent_id')
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
            'scores' => 'nullable|array',
            'scores.*' => 'numeric|min:0',
            'achievement_scores' => 'nullable|array',
            'achievement_scores.*' => 'numeric|min:0|max:50',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string',
        ]);

        $juriId = Auth::user()->lecturer->id;

        try {
            $this->assessmentService->saveScores(
                $registration->id,
                $juriId,
                $request->scores ?? [],
                $request->notes ?? [],
                $request->achievement_scores ?? []
            );
            $this->ahpCalculator->calculateFinalScore($registration);

            return redirect()->route('juri.assessments.index')->with('success', 'Nilai berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
