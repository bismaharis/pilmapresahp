<?php

use App\Models\Faculty;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;

function createAdminFacultyContext(): array
{
    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $admin = User::create([
        'name' => 'Admin Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $period = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(3)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    return compact('faculty', 'admin', 'period');
}

function createParticipant(Faculty $faculty, string $name, string $nim): Student
{
    $user = User::create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    return Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => $nim,
        'prodi' => 'Informatika',
        'semester' => 6,
        'ipk' => 3.75,
    ]);
}

test('admin fakultas bisa melihat histori periode beserta urutan pemenang', function () {
    $context = createAdminFacultyContext();

    $topStudent = createParticipant($context['faculty'], 'Mahasiswa Unggul', '22000001');
    $regularStudent = createParticipant($context['faculty'], 'Mahasiswa Biasa', '22000002');

    Registration::create([
        'period_id' => $context['period']->id,
        'student_id' => $regularStudent->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 85,
        'file_gk' => 'files/gk/biasa.pdf',
        'file_transkrip' => 'files/transkrip/biasa.pdf',
    ]);

    Registration::create([
        'period_id' => $context['period']->id,
        'student_id' => $topStudent->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 95,
        'file_gk' => 'files/gk/unggul.pdf',
        'file_transkrip' => 'files/transkrip/unggul.pdf',
    ]);

    $response = $this->actingAs($context['admin'])
        ->get(route('admin.periods.show', $context['period']));

    $response->assertOk();
    $response->assertSee('Pendaftar, Berkas, dan Urutan Pemenang');
    $response->assertSeeInOrder(['Mahasiswa Unggul', 'Mahasiswa Biasa']);
    $response->assertSee('#1');
});

test('admin fakultas bisa export histori periode ke excel csv', function () {
    $context = createAdminFacultyContext();

    $student = createParticipant($context['faculty'], 'Mahasiswa Export', '22000100');

    Registration::create([
        'period_id' => $context['period']->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 90,
        'file_gk' => 'files/gk/export.pdf',
        'file_transkrip' => 'files/transkrip/export.pdf',
    ]);

    $response = $this->actingAs($context['admin'])
        ->get(route('admin.periods.export_excel', $context['period']));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)->toContain('Urutan Pemenang');
    expect($content)->toContain('Mahasiswa Export');
    expect($content)->toContain('/storage/files/gk/export.pdf');
});

test('periode yang sudah berjalan tidak bisa dihapus agar histori tetap ada', function () {
    $context = createAdminFacultyContext();

    $response = $this->actingAs($context['admin'])
        ->from(route('admin.periods.index'))
        ->delete(route('admin.periods.destroy', $context['period']));

    $response->assertRedirect(route('admin.periods.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('pilmapres_periods', [
        'id' => $context['period']->id,
    ]);
});
