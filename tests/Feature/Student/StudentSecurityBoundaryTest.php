<?php

use App\Models\Achievement;
use App\Models\Faculty;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function buildStudentBoundaryContext(): array
{
    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $user = User::create([
        'name' => 'Mahasiswa Uji Batas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '2211000001',
        'prodi' => 'Informatika',
        'semester' => 6,
        'ipk' => 3.76,
    ]);

    $period = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(3)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    $registration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'draft',
    ]);

    return compact('faculty', 'user', 'student', 'period', 'registration');
}

test('mahasiswa tanpa profil student tidak bisa update pendaftaran', function () {
    $faculty = Faculty::create([
        'name' => 'Fakultas Hukum',
        'slug' => 'fakultas-hukum',
    ]);

    $user = User::create([
        'name' => 'Mahasiswa Belum Lengkap',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->from(route('student.registration.index'))
        ->put(route('student.registration.update'));

    $response
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('error');
});

test('mahasiswa tanpa profil student tidak bisa tambah capaian unggulan', function () {
    $faculty = Faculty::create([
        'name' => 'Fakultas Ekonomi',
        'slug' => 'fakultas-ekonomi',
    ]);

    $user = User::create([
        'name' => 'Mahasiswa CU Belum Lengkap',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->from(route('student.achievements.index'))
        ->post(route('student.achievements.store'), [
            'name' => 'Lomba KTI',
            'capaian' => 'Finalis',
            'category' => 'Kompetisi',
            'organizer' => 'Kemdikbud',
            'year' => now()->year,
            'type' => 'Individu',
            'jumlah_peserta' => 1,
            'jumlah_penghargaan' => '1',
            'level' => 'Nasional',
            'file_proof' => UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'),
        ]);

    $response
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('error');
});

test('file bukti capaian dibersihkan saat validasi batas item gagal', function () {
    Storage::fake('public');

    $context = buildStudentBoundaryContext();

    for ($index = 1; $index <= 10; $index++) {
        Achievement::create([
            'registration_id' => $context['registration']->id,
            'name' => 'Prestasi '.$index,
            'capaian' => 'Juara '.$index,
            'category' => 'Kompetisi',
            'organizer' => 'Panitia '.$index,
            'year' => now()->year,
            'type' => 'Individu',
            'jumlah_peserta' => 1,
            'jumlah_penghargaan' => '1',
            'level' => 'Nasional',
            'file_proof' => 'proofs/existing-'.$index.'.pdf',
        ]);
    }

    $response = $this->actingAs($context['user'])
        ->from(route('student.achievements.index'))
        ->post(route('student.achievements.store'), [
            'name' => 'Prestasi Baru',
            'capaian' => 'Juara',
            'category' => 'Kompetisi',
            'organizer' => 'Panitia',
            'year' => now()->year,
            'type' => 'Individu',
            'jumlah_peserta' => 1,
            'jumlah_penghargaan' => '1',
            'level' => 'Nasional',
            'file_proof' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
        ]);

    $response
        ->assertRedirect(route('student.achievements.index'))
        ->assertSessionHasErrors('limit');

    expect(Storage::disk('public')->allFiles('proofs'))->toHaveCount(0);
});

test('hapus capaian menghapus file bukti dari storage', function () {
    Storage::fake('public');

    $context = buildStudentBoundaryContext();

    $proofPath = 'proofs/hapus-saya.pdf';
    Storage::disk('public')->put($proofPath, 'dummy-content');

    $achievement = Achievement::create([
        'registration_id' => $context['registration']->id,
        'name' => 'Prestasi Hapus',
        'capaian' => 'Juara 1',
        'category' => 'Kompetisi',
        'organizer' => 'Panitia',
        'year' => now()->year,
        'type' => 'Individu',
        'jumlah_peserta' => 1,
        'jumlah_penghargaan' => '1',
        'level' => 'Nasional',
        'file_proof' => $proofPath,
    ]);

    $this->actingAs($context['user'])
        ->delete(route('student.achievements.destroy', $achievement->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('achievements', [
        'id' => $achievement->id,
    ]);

    expect(Storage::disk('public')->exists($proofPath))->toBeFalse();
});

test('halaman pendaftaran menampilkan flash error upload terlalu besar', function () {
    $context = buildStudentBoundaryContext();

    $response = $this->actingAs($context['user'])
        ->withSession([
            'error' => 'Ukuran total upload melebihi batas server. Naikkan upload_max_filesize dan post_max_size pada PHP.',
        ])
        ->get(route('student.registration.index'));

    $response->assertSee('Ukuran total upload melebihi batas server. Naikkan upload_max_filesize dan post_max_size pada PHP.');
});

test('halaman capaian menampilkan flash error upload terlalu besar', function () {
    $context = buildStudentBoundaryContext();

    $response = $this->actingAs($context['user'])
        ->withSession([
            'error' => 'Ukuran total upload melebihi batas server. Naikkan upload_max_filesize dan post_max_size pada PHP.',
        ])
        ->get(route('student.achievements.index'));

    $response->assertSee('Ukuran total upload melebihi batas server. Naikkan upload_max_filesize dan post_max_size pada PHP.');
});

test('update berkas mahasiswa memprioritaskan registration periode aktif bukan riwayat lama', function () {
    Storage::fake('public');

    $faculty = Faculty::create([
        'name' => 'Fakultas Kedokteran',
        'slug' => 'fakultas-kedokteran',
    ]);

    $user = User::create([
        'name' => 'Mahasiswa Update Aktif',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '2211999911',
        'prodi' => 'Kedokteran',
        'semester' => 6,
        'ipk' => 3.8,
    ]);

    $oldPeriod = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2025',
        'is_active' => false,
        'start_date' => now()->subYear()->subDays(10)->toDateString(),
        'end_date' => now()->subYear()->toDateString(),
    ]);

    $activePeriod = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(4)->toDateString(),
    ]);

    $historicalUniversityRegistration = Registration::create([
        'period_id' => $oldPeriod->id,
        'student_id' => $student->id,
        'stage' => 'universitas',
        'status' => 'submitted',
        'file_gk' => 'files/gk/riwayat-gk.pdf',
        'file_transkrip' => 'files/transkrip/riwayat-transkrip.pdf',
    ]);

    $activeFacultyRegistration = Registration::create([
        'period_id' => $activePeriod->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'submitted',
        'file_gk' => 'files/gk/aktif-lama.pdf',
        'file_transkrip' => 'files/transkrip/aktif-lama.pdf',
    ]);

    $this->actingAs($user)
        ->from(route('student.registration.index'))
        ->put(route('student.registration.update'), [
            'file_gk' => UploadedFile::fake()->create('gk-baru.pdf', 1000, 'application/pdf'),
            'file_transkrip' => UploadedFile::fake()->create('transkrip-baru.pdf', 1000, 'application/pdf'),
        ])
        ->assertRedirect(route('student.registration.index'));

    $activeFacultyRegistration->refresh();
    $historicalUniversityRegistration->refresh();

    expect($activeFacultyRegistration->file_gk)->not->toBe('files/gk/aktif-lama.pdf');
    expect($activeFacultyRegistration->file_transkrip)->not->toBe('files/transkrip/aktif-lama.pdf');
    expect($historicalUniversityRegistration->file_gk)->toBe('files/gk/riwayat-gk.pdf');
    expect($historicalUniversityRegistration->file_transkrip)->toBe('files/transkrip/riwayat-transkrip.pdf');
});

test('status pendaftaran fakultas berubah menjadi submitted ketika berkas wajib lengkap', function () {
    Storage::fake('public');

    $context = buildStudentBoundaryContext();

    expect($context['registration']->status)->toBe('draft');

    $this->actingAs($context['user'])
        ->from(route('student.registration.index'))
        ->put(route('student.registration.update'), [
            'file_gk' => UploadedFile::fake()->create('gk.pdf', 900, 'application/pdf'),
            'file_transkrip' => UploadedFile::fake()->create('transkrip.pdf', 900, 'application/pdf'),
        ])
        ->assertRedirect(route('student.registration.index'));

    $context['registration']->refresh();

    expect($context['registration']->file_gk)->not->toBeNull();
    expect($context['registration']->file_transkrip)->not->toBeNull();
    expect($context['registration']->status)->toBe('submitted');
});
