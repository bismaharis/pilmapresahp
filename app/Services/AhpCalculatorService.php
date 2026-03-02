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
    
        // 1. Hitung Skor CU
        $cuScore = $this->calculateCUScore($registration, $globalWeights);
        
        // 2. Hitung Skor Juri (GK & BI) - Pastikan TIDAK menghitung ulang CU
        $juriScore = $this->calculateJuriScore($registration, $globalWeights);
        
        $finalScore = $cuScore + $juriScore;

        // Update skor ke database
        if ($registration->stage === 'fakultas') {
            $registration->update(['total_score_fakultas' => $finalScore]);
        } else {
            $registration->update(['total_score_univ' => $finalScore]);
        }

        return $finalScore;
    }

    private function calculateGlobalWeights(): array
    {
        $criterias = Criteria::with('children')->whereNull('parent_id')->get();
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
        $totalCuScore = 0;
        // Ambil kriteria yang bertipe 'cu' dan merupakan leaf node (tidak punya anak)
        $cuCriterias = Criteria::where('type', 'cu')->doesntHave('children')->get();

        foreach ($cuCriterias as $criteria) {
            $assessment = Assessment::where('registration_id', $registration->id)
                ->where('criteria_id', $criteria->id)
                ->first();

            $rawScore = $assessment ? $assessment->score : 0;

            // NORMALISASI: Gunakan max_score dari database, bukan hardcoded 50
            $maxScore = $criteria->max_score > 0 ? $criteria->max_score : 50; 
            $normalized = ($rawScore / $maxScore) * 100;

            $globalWeight = $globalWeights[$criteria->id] ?? 0;
            $totalCuScore += ($normalized * $globalWeight);
        }

        return $totalCuScore;
    }

    private function calculateJuriScore(Registration $registration, array $globalWeights): float
    {
        $totalScore = 0;

        // PERBAIKAN: Tambahkan filter 'whereHas' untuk mengecualikan tipe 'cu'
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