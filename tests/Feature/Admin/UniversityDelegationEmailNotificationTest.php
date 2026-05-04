<?php

use App\Mail\UniversityDelegationCongratulationsMail;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

function createFacultyWithSlug(string $name, string $slug): Faculty
{
    return Faculty::create([
        'name' => $name,
        'slug' => $slug,
    ]);
}

function createStudentUserWithProfile(Faculty $faculty, string $name, string $nim): Student
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
        'ipk' => 3.8,
    ]);
}

function createPeriod(?int $facultyId = null): PilmapresPeriod
{
    return PilmapresPeriod::create([
        'faculty_id' => $facultyId,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(3)->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);
}

test('email selamat terkirim ke peserta saat didelegasikan ke universitas', function () {
    Mail::fake();

    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('output')->once()->andReturn('PDF_BINARY_PARTICIPANT');

    $faculty = createFacultyWithSlug('Fakultas Teknik', 'ft');

    $adminFaculty = User::create([
        'name' => 'Admin Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = createStudentUserWithProfile($faculty, 'Peserta Delegasi', '22030001');
    $period = createPeriod($faculty->id);

    $registration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 91,
        'file_gk' => 'files/gk/peserta.pdf',
        'file_transkrip' => 'files/transkrip/peserta.pdf',
    ]);

    $response = $this->actingAs($adminFaculty)
        ->post(route('admin.ranking.delegate', $registration));

    $response->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'stage' => 'universitas',
    ]);

    Mail::assertSent(UniversityDelegationCongratulationsMail::class, function (UniversityDelegationCongratulationsMail $mail) use ($student): bool {
        return $mail->hasTo($student->user->email)
            && $mail->recipientRoleLabel === 'Peserta Pilmapres'
            && $mail->pdfBinary === 'PDF_BINARY_PARTICIPANT';
    });
});

test('email selamat terkirim ke juri saat dinaikkan menjadi juri universitas', function () {
    Mail::fake();

    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('output')->once()->andReturn('PDF_BINARY_JURY');

    $faculty = createFacultyWithSlug('Fakultas Ekonomi', 'fe');

    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);

    $juryUser = User::create([
        'name' => 'Dosen Delegasi',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $lecturer = Lecturer::create([
        'user_id' => $juryUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '1989001',
        'unit_kerja' => 'Fakultas Ekonomi',
        'is_univ_judge' => false,
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('superadmin.delegation.juries.toggle', $lecturer));

    $response->assertRedirect();

    $this->assertDatabaseHas('lecturers', [
        'id' => $lecturer->id,
        'is_univ_judge' => true,
    ]);

    Mail::assertSent(UniversityDelegationCongratulationsMail::class, function (UniversityDelegationCongratulationsMail $mail) use ($juryUser): bool {
        return $mail->hasTo($juryUser->email)
            && $mail->recipientRoleLabel === 'Juri Pilmapres'
            && $mail->pdfBinary === 'PDF_BINARY_JURY';
    });
});
