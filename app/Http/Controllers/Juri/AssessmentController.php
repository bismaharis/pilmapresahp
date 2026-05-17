<?php

namespace App\Http\Controllers\Juri;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Criteria;
use App\Models\PilmapresPeriod;
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
        $juri = $this->resolveLecturerProfile();

        // Base query untuk registration yang eligible dinilai
        $baseQuery = Registration::query()->where(function ($q) {
            $q->whereIn('status', ['submitted', 'verified', 'approved'])
                ->whereNotNull('file_gk')
                ->whereNotNull('file_transkrip');
        });

        // Filter berdasarkan jenis juri
        if ($juri->is_univ_judge) {
            // Juri universitas hanya lihat mahasiswa yang sudah lolos ke universitas
            $query = $baseQuery->where('stage', 'universitas');

            $activeUniversityPeriod = PilmapresPeriod::getActiveUniversityPeriod();
            if ($activeUniversityPeriod) {
                $query->whereHas('period', function ($periodQuery) use ($activeUniversityPeriod) {
                    $periodQuery->where('year', '=', $activeUniversityPeriod->year);
                });
            }
        } else {
            // Juri fakultas hanya lihat mahasiswa dari fakultasnya sendiri di tingkat fakultas
            $query = $baseQuery->where('stage', 'fakultas')
                ->whereHas('student', function ($q) use ($juri) {
                    $q->where('faculty_id', $juri->faculty_id);
                });

            $activeFacultyPeriod = PilmapresPeriod::getActivePeriodForFaculty((int) $juri->faculty_id);
            if ($activeFacultyPeriod) {
                $query->where('period_id', '=', $activeFacultyPeriod->id);
            }
        }

        // Eager load relationships AFTER filtering
        $registrations = $query->with(['student.user', 'achievements'])->get();

        $assessedRegistrationIds = Assessment::query()
            ->where('lecturer_id', $juri->id)
            ->pluck('registration_id');

        $sudahDinilai = $assessedRegistrationIds
            ->intersect($registrations->pluck('id'))
            ->values()
            ->toArray();

        return view('juri.assessment.index', compact('registrations', 'juri', 'sudahDinilai'));
    }

    public function edit(Registration $registration)
    {
        $juri = $this->resolveLecturerProfile();
        $registration = Registration::with('achievements', 'student.user')->findOrFail($registration->id);

        // $existingAssessments = Assessment::where('registration_id', $registration->id)
        //     ->where('lecturer_id', auth()->id())
        //     ->get()
        //     ->keyBy('criterion_id');

        $this->authorizeJuri($juri, $registration);

        $registration->load('achievements');

        $criteriaTree = Criteria::query()->where('parent_id', '=', null)
            ->with(['children.children.children'])
            ->get();

        $existingAssessments = Assessment::query()->where('registration_id', $registration->id)
            ->where('lecturer_id', $juri->id)
            ->get(['criteria_id', 'score', 'notes']);

        $existingScores = $existingAssessments
            ->pluck('score', 'criteria_id')
            ->toArray();

        $existingNotes = $existingAssessments
            ->pluck('notes', 'criteria_id')
            ->toArray();

        $existingAchievementScores = [];
        foreach ($existingAssessments as $assessment) {
            if (! is_string($assessment->notes) || $assessment->notes === '') {
                continue;
            }

            $decodedNotes = json_decode($assessment->notes, true);
            if (! is_array($decodedNotes)) {
                continue;
            }

            $achievementScores = $decodedNotes['achievement_scores'] ?? null;
            if (! is_array($achievementScores)) {
                continue;
            }

            foreach ($achievementScores as $achievementId => $scoreValue) {
                $existingAchievementScores[(int) $achievementId] = (float) $scoreValue;
            }
        }

        return view('juri.assessment.edit', compact('registration', 'criteriaTree', 'existingScores', 'existingNotes', 'existingAchievementScores'));
    }

    public function update(Request $request, Registration $registration)
    {
        $juri = $this->resolveLecturerProfile();

        $this->authorizeJuri($juri, $registration);

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0',
            'achievement_scores' => 'nullable|array',
            'achievement_scores.*' => 'numeric|min:0|max:50',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string|max:2000',
        ]);

        $leafNonCuCriteria = Criteria::query()
            ->where('type', '!=', 'cu')
            ->doesntHave('children')
            ->get(['id', 'max_score'])
            ->keyBy('id');

        foreach (($request->scores ?? []) as $criteriaId => $scoreValue) {
            $criteria = $leafNonCuCriteria->get((int) $criteriaId);
            if (! $criteria) {
                return back()->withErrors([
                    'scores' => 'Terdapat kriteria penilaian yang tidak valid.',
                ])->withInput();
            }

            if ((float) $scoreValue > (float) $criteria->max_score) {
                return back()->withErrors([
                    "scores.$criteriaId" => 'Nilai melebihi batas maksimum kriteria.',
                ])->withInput();
            }
        }

        $allowedAchievementIds = $registration->achievements()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $submittedAchievementIds = array_map('strval', array_keys($request->achievement_scores ?? []));
        $unknownAchievementIds = array_diff($submittedAchievementIds, $allowedAchievementIds);

        if (! empty($unknownAchievementIds)) {
            return back()->withErrors([
                'achievement_scores' => 'Terdapat data capaian unggulan yang tidak valid.',
            ])->withInput();
        }

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
        $isEligibleRegistration = in_array($registration->status, ['submitted', 'verified', 'approved'], true)
            && $registration->file_gk
            && $registration->file_transkrip;

        if (! $isEligibleRegistration) {
            abort(403, 'Akses Ditolak: Peserta belum memenuhi syarat untuk dinilai.');
        }

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

    private function resolveLecturerProfile()
    {
        $lecturer = Auth::user()?->lecturer;
        if (! $lecturer) {
            abort(403, 'Akun Anda belum terhubung ke profil dosen. Hubungi admin.');
        }

        return $lecturer;
    }
}
