<?php

use App\Services\AhpMatrixService;

it('calculates ahp weights and consistency correctly with a sample pairwise matrix', function () {
    $mockComparison = Mockery::mock('alias:App\\Models\\AhpComparison');
    $mockComparison->shouldReceive('whereIn')->once()->andReturnSelf();
    $mockComparison->shouldReceive('whereIn')->once()->andReturnSelf();
    $mockComparison->shouldReceive('get')->once()->andReturn(collect([]));

    $service = new AhpMatrixService;

    // Matriks pairwise contoh (kategorinya C1, C2, C3)
    // C1:C2=2, C1:C3=4, C2:C3=3
    $ids = [1, 2, 3];
    $matrixValues = [
        1 => [2 => 2.0, 3 => 4.0],
        2 => [1 => 0.5, 3 => 3.0],
        3 => [1 => 0.25, 2 => 0.3333],
    ];

    $result = $service->previewCalculation($ids, $matrixValues);

    expect($result['n'])->toEqual(3);
    expect($result['lambda_max'])->toBeNumeric();
    expect($result['ci'])->toBeNumeric();
    expect($result['ri'])->toEqual(0.58);
    expect($result['cr'])->toBeLessThanOrEqual(0.1);
    expect($result['is_consistent'])->toBeTrue();

    expect($result['weights'][1])->toBeGreaterThan($result['weights'][2]);
    expect($result['weights'][2])->toBeGreaterThan($result['weights'][3]);

    // Bobot harus menjumlah hampir 1
    expect(abs(array_sum($result['weights']) - 1.0))->toBeLessThan(0.0001);
});
