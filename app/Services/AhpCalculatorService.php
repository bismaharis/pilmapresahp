<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Criteria;
use App\Models\Registration;

class AhpCalculatorService
{
    protected AhpMatrixService $ahpService;

    public function __construct(?AhpMatrixService $ahpService = null)
    {
        $this->ahpService = $ahpService ?? new AhpMatrixService;
    }

    public function calculateFinalScore(Registration $registration): float
    {
        $globalWeights = $this->ahpService->calculateGlobalWeights();

        $cuScore = $this->calculateCUScore($registration, $globalWeights);

        $juriScore = $this->calculateJuriScore($registration, $globalWeights);

        $finalScore = $cuScore + $juriScore;

        if ($registration->stage === 'fakultas') {
            $registration->update(['total_score_fakultas' => $finalScore]);
        } else {
            $registration->update(['total_score_univ' => $finalScore]);
        }

        return $finalScore;
    }

    public function calculateCUScore(Registration $registration, array $globalWeights): float
    {
        $registrationId = $registration?->id;
        if (! $registrationId) {
            return 0;
        }

        $cuCriterias = Criteria::where('type', 'cu')->doesntHave('children')->get();
        if ($cuCriterias->isEmpty()) {
            return 0;
        }

        $cuCriteriaIds = $cuCriterias->pluck('id')->toArray();
        $assessments = Assessment::where('registration_id', $registrationId)
            ->whereIn('criteria_id', $cuCriteriaIds)
            ->get()
            ->keyBy('criteria_id');

        // total skor mentah semua sub‑kriteria CU (jawaban juri per kategori)
        $totalRaw = 0;
        foreach ($cuCriterias as $criteria) {
            $assessment = $assessments->get($criteria->id);
            $score = $assessment?->score ?? 0;
            $totalRaw += max(0, (float) $score);
        }

        // jangan melebihi batas maksimal kumulatif
        $totalRaw = min($totalRaw, 500);

        // gunakan denominator tetap 500 sesuai formula bisnis
        // (total_raw / 500) * 100 * bobot_root
        $totalRaw = min($totalRaw, 500);
        if ($totalRaw < 0) {
            $totalRaw = 0;
        }

        $cuRoot = Criteria::where('type', 'cu')->whereNull('parent_id')->first();
        $weight = $cuRoot?->weight ?? 0.35;
        if ($weight <= 0) {
            $weight = 0.35;
        }

        $cuScore = ($totalRaw / 500.0) * 100.0 * (float) $weight;

        return max(0, (float) $cuScore);
    }

    public function calculateJuriScore(Registration $registration, array $globalWeights): float
    {
        $registrationId = $registration?->id;
        if (! $registrationId) {
            return 0;
        }

        $totalScore = 0;

        $assessmentsGrouped = Assessment::where('registration_id', $registrationId)
            ->whereHas('criteria', function ($query) {
                $query->where('type', '!=', 'cu');
            })
            ->get()
            ->groupBy('criteria_id');

        if ($assessmentsGrouped->isEmpty()) {
            return 0;
        }

        $criteriaIds = $assessmentsGrouped->keys()->toArray();
        $criterias = Criteria::whereIn('id', $criteriaIds)->get()->keyBy('id');

        foreach ($assessmentsGrouped as $criteriaId => $scores) {
            if ($scores->isEmpty()) {
                continue;
            }

            $averageRaw = (float) ($scores->avg('score') ?? 0);
            if ($averageRaw < 0) {
                $averageRaw = 0;
            }

            $criteria = $criterias->get($criteriaId);
            if (! $criteria || ($criteria->max_score ?? 0) <= 0) {
                continue;
            }

            $maxScore = (float) $criteria->max_score;
            $normalized = ($averageRaw / $maxScore) * 100.0;
            $normalized = min(100, max(0, $normalized));

            $globalWeight = (float) ($globalWeights[$criteriaId] ?? 0);
            $totalScore += ($normalized * $globalWeight);
        }

        return max(0, (float) $totalScore);
    }
}
