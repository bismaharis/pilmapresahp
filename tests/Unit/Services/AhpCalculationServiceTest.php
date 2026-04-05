<?php

use App\Services\AhpCalculationService;
use Mockery;

// ==================== calculateWeights Tests ====================

test('calculateWeights with normal 3x3 matrix returns normalized weights', function () {
    $service = new AhpCalculationService;

    // Path: A, B, C - normal flow dengan product > 0
    $matrix = [
        [1, 2, 4],
        [0.5, 1, 3],
        [0.25, 0.333, 1],
    ];

    $weights = $service->calculateWeights($matrix);

    // Weights harus sum ~= 1.0
    expect(count($weights))->toEqual(3);
    expect(abs(array_sum($weights) - 1.0))->toBeLessThan(0.0001);
    // First weight harus paling besar (1 paling dominan)
    expect($weights[0])->toBeGreaterThan($weights[1]);
    expect($weights[1])->toBeGreaterThan($weights[2]);
});

test('calculateWeights with empty matrix returns empty array', function () {
    $service = new AhpCalculationService;

    // Path: D1 - n == 0 decision
    $matrix = [];

    $weights = $service->calculateWeights($matrix);

    expect($weights)->toEqual([]);
});

test('calculateWeights with single element', function () {
    $service = new AhpCalculationService;

    // Edge case: n=1
    $matrix = [[1]];

    $weights = $service->calculateWeights($matrix);

    expect(count($weights))->toEqual(1);
    expect($weights[0])->toEqual(1.0);
});

test('calculateWeights with 2x2 matrix', function () {
    $service = new AhpCalculationService;

    // Path: B, C - loop coverage dengan n=2
    $matrix = [
        [1, 3],
        [0.333, 1],
    ];

    $weights = $service->calculateWeights($matrix);

    expect(count($weights))->toEqual(2);
    expect(abs(array_sum($weights) - 1.0))->toBeLessThan(0.0001);
    expect($weights[0])->toBeGreaterThan($weights[1]);
});

test('calculateWeights throws exception on matrix with non-positive value', function () {
    $service = new AhpCalculationService;

    $matrix = [
        [1, 0, 4],    // contains zero value
        [0.5, 1, 3],
        [0.25, 0.333, 1],
    ];

    expect(fn () => $service->calculateWeights($matrix))
        ->toThrow(\InvalidArgumentException::class, 'Geometric mean invalid');
});

// ==================== calculateConsistencyRatio Tests ====================

test('calculateConsistencyRatio with n <= 2 returns 0', function () {
    $service = new AhpCalculationService;

    // Path: D1 - n <= 2 early return
    $matrix = [[1, 3], [0.333, 1]];
    $weights = [0.75, 0.25];

    $cr = $service->calculateConsistencyRatio($matrix, $weights);

    expect($cr)->toEqual(0);
});

test('calculateConsistencyRatio with n=1 returns 0', function () {
    $service = new AhpCalculationService;

    // Edge case: n=1
    $matrix = [[1]];
    $weights = [1.0];

    $cr = $service->calculateConsistencyRatio($matrix, $weights);

    expect($cr)->toEqual(0);
});

test('calculateConsistencyRatio with consistent 3x3 matrix', function () {
    $service = new AhpCalculationService;

    // Path: A, B, C - normal flow untuk n > 2
    $matrix = [
        [1, 2, 4],
        [0.5, 1, 3],
        [0.25, 0.333, 1],
    ];
    $weights = [0.539, 0.297, 0.164];

    $cr = $service->calculateConsistencyRatio($matrix, $weights);

    // CR harus < 0.1 untuk consistent
    expect($cr)->toBeLessThan(0.1);
    expect($cr)->toBeGreaterThanOrEqual(0);
});

test('calculateConsistencyRatio handles weights perfectly (lambda_max = n)', function () {
    $service = new AhpCalculationService;

    // Ideal case: perfect consistency, lambda_max = n
    $matrix = [
        [1, 2],
        [0.5, 1],
    ];
    $weights = [0.667, 0.333]; // Perfectly consistent weights

    // For n=2, returns 0 immediately
    $cr = $service->calculateConsistencyRatio($matrix, $weights);
    expect($cr)->toEqual(0);
});

test('calculateConsistencyRatio with zero weight throws invalid argument', function () {
    $service = new AhpCalculationService;

    $matrix = [
        [1, 2, 4],
        [0.5, 1, 3],
        [0.25, 0.333, 1],
    ];
    $weights = [0, 0.5, 0.5];

    expect(fn () => $service->calculateConsistencyRatio($matrix, $weights))
        ->toThrow(\InvalidArgumentException::class, 'Weights invalid');
});

// ==================== buildComparisonMatrix Tests ====================

test('buildComparisonMatrix initializes with diagonal of 1', function () {
    $service = new AhpCalculationService;

    // Mock PairwiseComparison query builder to avoid DB
    $mock = Mockery::mock('alias:App\\Models\\PairwiseComparison');
    $mock->shouldReceive('whereIn')->once()->andReturnSelf();
    $mock->shouldReceive('whereIn')->once()->andReturnSelf();
    $mock->shouldReceive('get')->once()->andReturn(collect([]));

    // Path: Initialize matrix dengan diagonal 1
    // Diagonal elements harus selalu 1
    $criteriaIds = [1, 2, 3];

    $matrix = $service->buildComparisonMatrix($criteriaIds);

    // Diagonal should all be 1 (initialized)
    expect($matrix[0][0])->toEqual(1);
    expect($matrix[1][1])->toEqual(1);
    expect($matrix[2][2])->toEqual(1);

    // Matrix should be 3x3
    expect(count($matrix))->toEqual(3);
    expect(count($matrix[0]))->toEqual(3);
})->group('unit', 'buildsMatrix');

// ==================== Global Weights Recursive Tests ====================

test('calculateGlobalWeights with mock criteria returns weighted structure', function () {
    $service = new AhpCalculationService;

    // Integration test: Full hierarchy traversal
    // Require actual DB setup dengan criteria tree

    // Mock or use seeded data
    // This is more of an integration test
});

// ==================== Aggregated Test Cases ====================

test('AhpCalculationService handles consistent matrix throughout flow', function () {
    $service = new AhpCalculationService;

    // Complete flow: weights → CR → validation
    $matrix = [
        [1, 2, 4],
        [0.5, 1, 3],
        [0.25, 0.333, 1],
    ];

    $weights = $service->calculateWeights($matrix);
    $cr = $service->calculateConsistencyRatio($matrix, $weights);

    expect($weights)->toHaveCount(3);
    expect(abs(array_sum($weights) - 1.0))->toBeLessThan(0.0001);
    expect($cr)->toBeLessThan(0.1); // Consistent
});

test('AhpCalculationService handles different matrix sizes', function () {
    $service = new AhpCalculationService;

    // Loop coverage: different values of n
    foreach ([1, 2, 3, 4, 5] as $n) {
        // Create valid reciprocal matrix
        $matrix = array_fill(0, $n, array_fill(0, $n, 1.0));

        $weights = $service->calculateWeights($matrix);

        expect($weights)->toHaveCount($n);
        expect(abs(array_sum($weights) - 1.0))->toBeLessThan(0.0001);
    }
});
