<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Criteria;
use App\Models\Assessment;
// use Illuminate\Support\Facades\DB;

class AhpCalculatorService
{
    public function calculateFinalScore(Registration $registration): float
    {
        $globalWeights = $this->calculateGlobalWeights();
    
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

    private function calculateGlobalWeights(): array
    {
        $criterias = Criteria::with('children')->whereNull('parent_id')->where('type', '!=', 'cu')->get();
        $weights = [];

        foreach ($criterias as $root) {
            $this->traverseAndComputeWeight($root, $root->weight, $weights);
        }

        return $weights;
    }

    private function traverseAndComputeWeight(Criteria $criteria, float $currentWeight, array &$weights): void
    {
        if ($criteria->children->isEmpty()) {
            $weights[$criteria->id] = $currentWeight;
            return;
        }

        foreach ($criteria->children as $child) {
            $this->traverseAndComputeWeight($child, $currentWeight * $child->weight, $weights);
        }
    }

    private function calculateCUScore(Registration $registration, array $globalWeights): float
    {
        $cuCriterias = Criteria::where('type', 'cu')->doesntHave('children')->get();

        // total skor mentah semua sub‑kriteria CU (jawaban juri per kategori)
        $totalRaw = 0;
        foreach ($cuCriterias as $criteria) {
            $assessment = Assessment::where('registration_id', $registration->id)
                ->where('criteria_id', $criteria->id)
                ->first();

            $totalRaw += $assessment ? $assessment->score : 0;
        }

        // jangan melebihi batas maksimal kumulatif
        $totalRaw = min($totalRaw, 500);

        // gunakan denominator tetap 500 sesuai formula bisnis
        // (total_raw / 500) * 100 * bobot_root
        $cuRoot = Criteria::where('type', 'cu')->whereNull('parent_id')->first();
        $weight   = $cuRoot ? $cuRoot->weight : 0.35;

        $cuScore = ($totalRaw / 500) * 100 * $weight;

        return $cuScore;
    }

    private function calculateJuriScore(Registration $registration, array $globalWeights): float
    {
        $totalScore = 0;

        $assessments = Assessment::where('registration_id', $registration->id)
            ->whereHas('criteria', function($query) {
                $query->where('type', '!=', 'cu');
            })
            ->get()
            ->groupBy('criteria_id');

        foreach ($assessments as $criteriaId => $scores) {
            $averageRaw = $scores->avg('score') ?? 0;

            $criteria = Criteria::find($criteriaId);
            if (!$criteria || $criteria->max_score <= 0) continue;

            $normalized = ($averageRaw / $criteria->max_score) * 100;

            $globalWeight = $globalWeights[$criteriaId] ?? 0;
            $totalScore += ($normalized * $globalWeight);
        }

        return $totalScore;
    }
}