<?php

namespace App\Services;

use App\Models\AhpComparison;
use App\Models\Criteria;
use Illuminate\Support\Collection;

class AhpMatrixService
{
    private const RANDOM_INDEX = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
        11 => 1.51,
        12 => 1.54,
        13 => 1.56,
        14 => 1.57,
        15 => 1.58,
    ];

    private const CR_THRESHOLD = 0.10;

    public function recalculateAllWeights(): array
    {
        $results = [];

        $roots = Criteria::whereNull('parent_id')->get();
        $results['root'] = $this->processLevel($roots);

        if ($roots->count() === 1) {
            $roots->first()->update(['weight' => 1.0, 'cr_value' => 0, 'cr_status' => 'consistent']);
        }

        $this->recalculateChildren($roots, $results);

        return $results;
    }

    public function recalculateLevelByParent(?int $parentId): array
    {
        $siblings = Criteria::where('parent_id', $parentId)->get();

        if ($siblings->isEmpty()) {
            return ['status' => 'skipped', 'reason' => 'Tidak ada kriteria pada level ini.'];
        }

        return $this->processLevel($siblings);
    }

    public function previewCalculation(array $criteriaIds, array $matrixValues = []): array
    {
        $n = count($criteriaIds);
        if ($n === 0) {
            throw new \InvalidArgumentException('Tidak ada kriteria untuk dikalkulasi.');
        }

        $matrix = $this->buildMatrix($criteriaIds, $matrixValues);

        return $this->calculate($criteriaIds, $matrix);
    }

    private function processLevel(Collection $criterias): array
    {
        $n = $criterias->count();

        if ($n === 1) {
            $criterias->first()->update([
                'weight' => 1.0,
                'cr_value' => 0.0,
                'cr_status' => 'consistent',
            ]);

            return [
                'criteria_count' => 1,
                'weights' => [$criterias->first()->id => 1.0],
                'lambda_max' => 1.0,
                'ci' => 0.0,
                'cr' => 0.0,
                'is_consistent' => true,
            ];
        }

        $ids = $criterias->pluck('id')->toArray();
        $matrix = $this->buildMatrix($ids);
        $result = $this->calculate($ids, $matrix);

        foreach ($criterias as $criteria) {
            $updateData = [
                'cr_value' => $result['cr'],
                'cr_status' => $result['is_consistent'] ? 'consistent' : 'inconsistent',
            ];

            // Simpan bobot hanya jika konsisten (CR <= 0.1)
            if ($result['is_consistent']) {
                $weight = (float) ($result['weights'][$criteria->id] ?? 0);
                $updateData['weight'] = max(0, min(1, $weight));
            }

            try {
                $criteria->update($updateData);
            } catch (\Exception $e) {
                \Log::error("Failed to update criteria {$criteria->id}: {$e->getMessage()}");
            }
        }

        return array_merge($result, ['criteria_count' => $n]);
    }

    private function recalculateChildren(Collection $criterias, array &$results): void
    {
        foreach ($criterias as $criteria) {
            $children = Criteria::where('parent_id', $criteria->id)->get();
            if ($children->isEmpty()) {
                continue;
            }

            $key = 'criteria_'.$criteria->id.'_children';
            $results[$key] = $this->processLevel($children);
            $this->recalculateChildren($children, $results);
        }
    }

    private function buildMatrix(array $ids, array $overrideValues = []): array
    {
        $n = count($ids);
        $matrix = [];

        $comparisonMap = $this->loadComparisonMap($ids);

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $idI = $ids[$i];
                $idJ = $ids[$j];

                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;

                    continue;
                }

                if (! empty($overrideValues) && isset($overrideValues[$idI][$idJ])) {
                    $value = (float) $overrideValues[$idI][$idJ];
                } elseif (isset($comparisonMap[$idI][$idJ])) {
                    $value = $comparisonMap[$idI][$idJ];
                } elseif (isset($comparisonMap[$idJ][$idI]) && $comparisonMap[$idJ][$idI] > 0) {
                    $value = 1.0 / $comparisonMap[$idJ][$idI];
                } else {
                    $value = 1.0;
                }

                $matrix[$i][$j] = $value;
            }
        }

        return $matrix;
    }

    private function loadComparisonMap(array $ids): array
    {
        $comparisons = AhpComparison::whereIn('criteria_id_1', $ids)
            ->whereIn('criteria_id_2', $ids)
            ->get();

        $map = [];
        foreach ($comparisons as $comp) {
            $map[$comp->criteria_id_1][$comp->criteria_id_2] = (float) $comp->value;
        }

        return $map;
    }

    private function calculate(array $ids, array $matrix): array
    {
        $n = count($ids);
        $columnSums = array_fill(0, $n, 0.0);

        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $columnSums[$j] += $matrix[$i][$j];
            }
        }

        $normalizedMatrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $normalizedMatrix[$i][$j] = ($columnSums[$j] > 0) ? $matrix[$i][$j] / $columnSums[$j] : 0.0;
            }
        }

        $weights = [];
        $weightSum = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += $normalizedMatrix[$i][$j];
            }

            $w = $rowSum / $n;
            $weights[$ids[$i]] = $w;
            $weightSum += $w;
        }

        if ($weightSum > 0) {
            foreach ($weights as $id => $w) {
                $weights[$id] = $w / $weightSum;
            }
        }

        $lambdaMax = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $lambdaMax += $columnSums[$j] * ($weights[$ids[$j]] ?? 0.0);
        }

        $ci = ($n > 1) ? ($lambdaMax - $n) / ($n - 1) : 0.0;
        $ri = self::RANDOM_INDEX[$n] ?? 1.58;
        $cr = ($ri > 0) ? $ci / $ri : 0.0;
        $isConsistent = $cr <= self::CR_THRESHOLD;

        return [
            'weights' => $weights,
            'column_sums' => $columnSums,
            'lambda_max' => round($lambdaMax, 6),
            'ci' => round($ci, 6),
            'ri' => $ri,
            'cr' => round($cr, 6),
            'is_consistent' => $isConsistent,
            'n' => $n,
            'matrix' => $matrix,
            'normalized' => $normalizedMatrix,
        ];
    }

    public function calculateGlobalWeights(): array
    {
        $globalWeights = [];

        $roots = Criteria::whereNull('parent_id')->with(['children.children.children'])->get();

        foreach ($roots as $root) {
            $this->traverseGlobalWeight($root, $root->weight, $globalWeights);
        }

        return $globalWeights;
    }

    private function traverseGlobalWeight(Criteria $criteria, float $parentGlobalWeight, array &$globalWeights): void
    {
        $currentGlobal = $parentGlobalWeight;

        if ($criteria->children->isEmpty()) {
            $globalWeights[$criteria->id] = $currentGlobal;

            return;
        }

        foreach ($criteria->children as $child) {
            $childGlobal = $currentGlobal * $child->weight;
            $this->traverseGlobalWeight($child, $childGlobal, $globalWeights);
        }
    }

    public function getRandomIndex(int $n): float
    {
        return self::RANDOM_INDEX[$n] ?? 1.58;
    }

    public function getCrThreshold(): float
    {
        return self::CR_THRESHOLD;
    }
}
