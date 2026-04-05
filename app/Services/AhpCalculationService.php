<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\PairwiseComparison;

class AhpCalculationService
{
    /**
     * Hitung bobot prioritas dari matriks perbandingan berpasangan
     * Menggunakan geometric mean method untuk eigenvector
     */
    public function calculateWeights(array $matrix): array
    {
        $n = count($matrix);
        if ($n == 0) {
            return [];
        }

        // Hitung geometric mean untuk setiap baris
        $geometricMeans = [];
        for ($i = 0; $i < $n; $i++) {
            $product = 1;
            for ($j = 0; $j < $n; $j++) {
                if (! isset($matrix[$i][$j]) || $matrix[$i][$j] <= 0) {
                    throw new \InvalidArgumentException('Geometric mean invalid: semua nilai matriks harus > 0.');
                }
                $product *= $matrix[$i][$j];
            }
            $geometricMeans[$i] = pow($product, 1 / $n);
        }

        // Validasi dimana semua geometric mean harus positif
        $sum = array_sum($geometricMeans);

        if ($sum <= 0.0) {
            throw new \InvalidArgumentException('Geometric mean invalid: sum<=0. Pastikan semua nilai matriks > 0.');
        }

        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            if ($geometricMeans[$i] < 0.0) {
                throw new \InvalidArgumentException('Geometric mean invalid: nilai negatif.');
            }
            $weights[$i] = $geometricMeans[$i] / $sum;
        }

        return $weights;
    }

    /**
     * Hitung Consistency Ratio (CR)
     */
    public function calculateConsistencyRatio(array $matrix, array $weights): float
    {
        $n = count($matrix);
        if ($n <= 2) {
            return 0;
        } // CR = 0 untuk n <= 2

        // Validasi bobot, jangan ada nol atau negatif.
        foreach ($weights as $w) {
            if ($w <= 0.0) {
                throw new \InvalidArgumentException('Weights invalid: nilai bobot harus > 0 untuk perhitungan CR.');
            }
        }

        // Hitung lambda max
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            $weightedSum = 0;
            for ($j = 0; $j < $n; $j++) {
                $weightedSum += $matrix[$i][$j] * $weights[$j];
            }
            if ($weights[$i] == 0.0) {
                continue;
            }
            $lambdaMax += $weightedSum / $weights[$i];
        }
        $lambdaMax /= $n;

        // Hitung CI
        $ci = ($lambdaMax - $n) / ($n - 1);

        // Random Index (RI) berdasarkan n
        $ri = $this->getRandomIndex($n);

        // CR
        $cr = $ri > 0 ? $ci / $ri : 0;

        return $cr;
    }

    /**
     * Get Random Index untuk n kriteria
     */
    private function getRandomIndex(int $n): float
    {
        $riValues = [
            1 => 0,
            2 => 0,
            3 => 0.58,
            4 => 0.9,
            5 => 1.12,
            6 => 1.24,
            7 => 1.32,
            8 => 1.41,
            9 => 1.45,
            10 => 1.49,
        ];

        return $riValues[$n] ?? 1.49; // default untuk n > 10
    }

    /**
     * Bangun matriks perbandingan dari pairwise comparisons
     */
    public function buildComparisonMatrix(array $criteriaIds): array
    {
        $n = count($criteriaIds);
        $matrix = array_fill(0, $n, array_fill(0, $n, 1)); // diagonal 1

        // Ambil pairwise comparisons
        $comparisons = PairwiseComparison::whereIn('criteria_id_1', $criteriaIds)
            ->whereIn('criteria_id_2', $criteriaIds)
            ->get();

        // Map criteria id ke index
        $idToIndex = array_flip($criteriaIds);

        foreach ($comparisons as $comp) {
            $i = $idToIndex[$comp->criteria_id_1];
            $j = $idToIndex[$comp->criteria_id_2];

            if ($comp->value <= 0) {
                continue; // value invalid, skip to prevent division by zero/negatif
            }

            $matrix[$i][$j] = (float) $comp->value;
            $matrix[$j][$i] = 1.0 / (float) $comp->value; // reciprocal
        }

        return $matrix;
    }

    /**
     * Hitung bobot global untuk semua kriteria leaf
     */
    public function calculateGlobalWeights(): array
    {
        $rootCriterias = Criteria::whereNull('parent_id')->where('type', '!=', 'cu')->get();
        $globalWeights = [];

        foreach ($rootCriterias as $root) {
            // Untuk root criteria, gunakan weight statis
            $this->calculateWeightsRecursive($root, $root->weight, $globalWeights);
        }

        return $globalWeights;
    }

    /**
     * Hitung bobot secara rekursif
     */
    private function calculateWeightsRecursive(Criteria $criteria, float $parentWeight, array &$globalWeights): void
    {
        if ($criteria->children->isEmpty()) {
            $globalWeights[$criteria->id] = $parentWeight;

            return;
        }

        // Ambil anak-anak
        $children = $criteria->children;
        $criteriaIds = $children->pluck('id')->toArray();

        // Bangun matriks perbandingan
        $matrix = $this->buildComparisonMatrix($criteriaIds);

        // Hitung bobot
        $weights = $this->calculateWeights($matrix);

        // Hitung CR
        $cr = $this->calculateConsistencyRatio($matrix, $weights);

        // Jika CR > 0.1, mungkin perlu notifikasi, tapi untuk sekarang lanjutkan

        // Rekursi ke anak-anak
        foreach ($children as $index => $child) {
            $childWeight = $weights[$index] ?? 0;
            $this->calculateWeightsRecursive($child, $parentWeight * $childWeight, $globalWeights);
        }
    }
}
