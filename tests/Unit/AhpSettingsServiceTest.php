<?php

use App\Services\AhpSettingsService;
use App\Repositories\Contracts\CriteriaRepositoryInterface;
use Mockery\MockInterface;

test('it converts percentage to decimal and updates repository', function () {
    // 1. Mock Repository (Kita pura-pura punya database)
    $mockRepo = Mockery::mock(CriteriaRepositoryInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('update')
            ->once() // Pastikan dipanggil 1 kali
            ->with(1, ['weight' => 0.6]) // Cek apakah 60 dikonversi jadi 0.6
            ->andReturn(true);
    });

    // 2. Panggil Service
    $service = new AhpSettingsService($mockRepo);
    
    // 3. Action: Update ID 1 jadi 60%
    $service->updateWeight(1, 60); 

    // Tidak ada assertion return karena void, tapi mock->shouldReceive->once() sudah memverifikasi.
    // Agar valid di Pest, kita bisa tambah expect true
    expect(true)->toBeTrue();
});

test('it throws exception if weight is invalid', function () {
    $mockRepo = Mockery::mock(CriteriaRepositoryInterface::class);
    $service = new AhpSettingsService($mockRepo);

    // Coba update 150% (harus error)
    expect(fn() => $service->updateWeight(1, 150))
        ->toThrow(Exception::class, "Bobot harus antara 0% sampai 100%");
});