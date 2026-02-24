<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Criteria;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

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
        
        $cuCriterias = Criteria::where('type', 'cu')->doesntHave('children')->get();
        $achievements = $registration->achievements()->get()->groupBy('category');

        foreach ($cuCriterias as $criteria) {
            $items = $achievements->get($criteria->name, collect());
            
            $rawScore = 0;
            foreach ($items->take(4) as $item) {
                $score = match($item->level) {
                    'Internasional' => 50,
                    'Nasional' => 40,
                    'Provinsi' => 30,
                    default => 20
                };
                $rawScore += $score;
            }

            $normalized = ($rawScore / 200) * 100;

            $globalWeight = $globalWeights[$criteria->id] ?? 0;
            $totalCuScore += ($normalized * $globalWeight);
        }

        return $totalCuScore;
    }

    private function calculateJuriScore(Registration $registration, array $globalWeights): float
    {
        $totalScore = 0;

        $assessments = Assessment::where('registration_id', $registration->id)
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