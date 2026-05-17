<?php

use App\Models\Faculty;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;

function createStudentRegistrationContext(array $registrationAttributes = []): array
{
    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $user = User::create([
        'name' => 'Mahasiswa Uji',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '1234567890',
        'prodi' => 'Informatika',
        'semester' => 6,
        'ipk' => 3.75,
    ]);

    $period = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(3)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    $registration = Registration::create(array_merge([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'draft',
    ], $registrationAttributes));

    return compact('faculty', 'user', 'student', 'period', 'registration');
}

function createActiveUniversityPeriod(string $year = '2026'): PilmapresPeriod
{
    return PilmapresPeriod::create([
        'faculty_id' => null,
        'year' => $year,
        'is_active' => true,
        'start_date' => now()->subDays(3)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);
}

test('banner fakultas tidak muncul sebelum simpan berkas', function () {
    $context = createStudentRegistrationContext();

    $response = $this->actingAs($context['user'])
        ->get(route('student.registration.index'));

    $response->assertOk();
    $response->assertDontSee('Anda telah terdaftar — Tahap Fakultas');
    $response->assertSee('file:bg-blue-600', false);
    $response->assertDontSee('file:bg-green-600', false);
});

test('tombol upload berubah hijau ketika berkas sudah ada', function () {
    $context = createStudentRegistrationContext([
        'file_gk' => 'files/gk/naskah.pdf',
        'file_transkrip' => 'files/transkrip/transkrip.pdf',
    ]);

    $response = $this->actingAs($context['user'])
        ->get(route('student.registration.index'));

    $response->assertOk();
    $response->assertSee('file:bg-green-600', false);
    $response->assertSee('hover:file:bg-green-700', false);
});

test('banner fakultas muncul setelah simpan berkas tahap fakultas', function () {
    $context = createStudentRegistrationContext();

    $this->actingAs($context['user'])
        ->put(route('student.registration.update'))
        ->assertRedirect();

    expect($context['registration']->fresh()->submitted_fakultas_at)->not->toBeNull();

    $response = $this->actingAs($context['user'])
        ->get(route('student.registration.index'));

    $response->assertOk();
    $response->assertSee('Anda telah terdaftar — Tahap Fakultas');
});

test('banner universitas hanya muncul setelah simpan berkas tahap universitas', function () {
    $context = createStudentRegistrationContext([
        'stage' => 'universitas',
        'submitted_fakultas_at' => now(),
    ]);

    createActiveUniversityPeriod();

    $beforeSaveResponse = $this->actingAs($context['user'])
        ->get(route('student.registration.index'));

    $beforeSaveResponse->assertOk();
    $beforeSaveResponse->assertDontSee('Anda telah terdaftar — Tahap Universitas');

    $this->actingAs($context['user'])
        ->put(route('student.registration.update'), [
            'video_link' => 'https://youtube.com/watch?v=test12345',
        ])
        ->assertRedirect();

    expect($context['registration']->fresh()->submitted_universitas_at)->not->toBeNull();

    $afterSaveResponse = $this->actingAs($context['user'])
        ->get(route('student.registration.index'));

    $afterSaveResponse->assertOk();
    $afterSaveResponse->assertSee('Anda telah terdaftar — Tahap Universitas');
}
);

test('submit tahap universitas ditolak ketika periode universitas tidak aktif', function () {
    $context = createStudentRegistrationContext([
        'stage' => 'universitas',
        'submitted_fakultas_at' => now(),
    ]);

    $response = $this->actingAs($context['user'])
        ->from(route('student.registration.index'))
        ->put(route('student.registration.update'), [
            'video_link' => 'https://youtube.com/watch?v=test12345',
        ]);

    $response
        ->assertRedirect(route('student.registration.index'))
        ->assertSessionHas('error', 'Pendaftaran ditutup. Tidak ada periode seleksi tingkat universitas yang sedang berjalan.');

    expect($context['registration']->fresh()->submitted_universitas_at)->toBeNull();
});
