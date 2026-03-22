<?php

/**
 * Helper functions untuk tampilan AHP di transparency/show.blade.php
 * Letakkan di: app/Helpers/AhpViewHelper.php
 * Daftarkan di composer.json → autoload → files
 */

if (!function_exists('computeGlobalWeights')) {
    function computeGlobalWeights($children, float $parentWeight, array &$result): void
    {
        foreach ($children as $child) {
            $gw = $parentWeight * $child->weight;
            $result[$child->id] = $gw;
            if ($child->children->isNotEmpty()) {
                computeGlobalWeights($child->children, $gw, $result);
            }
        }
    }
}

if (!function_exists('accumScore')) {
    function accumScore($node, $source, bool $isCollection, string $type): float
    {
        if ($node->children->isEmpty()) {
            $score = $isCollection
                ? ($source->get($node->id)?->score ?? 0)
                : ($source->where('criteria_id', $node->id)->avg('score') ?? 0);
            if ($type === 'cu') return 0;
            return $node->max_score > 0 ? ($score / $node->max_score) * 100 * $node->weight : 0;
        }
        $total = 0;
        foreach ($node->children as $child) {
            $total += accumScore($child, $source, $isCollection, $type);
        }
        return $total * $node->weight;
    }
}

if (!function_exists('fmt')) {
    function fmt(float $val, int $level = 0, bool $isGk = false): string
    {
        if ($isGk && $level >= 3) return number_format($val, 4);
        return number_format($val, 2);
    }
}

if (!function_exists('fmtWeight')) {
    function fmtWeight(float $gw, int $level = 0, bool $isGk = false): string
    {
        $pct = $gw * 100;
        if ($isGk && $level >= 2) return number_format($pct, 4) . '%';
        return number_format($pct, 2) . '%';
    }
}