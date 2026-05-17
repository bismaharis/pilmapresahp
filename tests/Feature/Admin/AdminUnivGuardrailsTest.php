<?php

use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\UniversityDelegationNotificationService;
use Illuminate\Support\Facades\Mail;

function createAdminUniv(): User
{
    return User::create([
        'name' => 'Admin Univ',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_univ',
        'email_verified_at' => now(),
    ]);
}

test('admin univ cannot delegate participant that is already in universitas stage', function () {
    Mail::fake();

    $adminUniv = createAdminUniv();

    $faculty = Faculty::create([
        'name' => 'Fakultas Matematika',
        'slug' => 'fakultas-matematika',
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Univ',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => '22110011',
        'prodi' => 'Statistika',
        'semester' => 6,
        'ipk' => 3.7,
    ]);

    $period = PilmapresPeriod::create([
        'faculty_id' => null,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $registration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'universitas',
        'status' => 'verified',
        'total_score_fakultas' => 89,
    ]);

    $response = $this->actingAs($adminUniv)
        ->from(route('admin.ranking.index'))
        ->post(route('admin.ranking.delegate', $registration));

    $response->assertRedirect(route('admin.ranking.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'stage' => 'universitas',
    ]);
});

test('admin univ cannot cancel delegate for participant that is still in fakultas stage', function () {
    $adminUniv = createAdminUniv();

    $faculty = Faculty::create([
        'name' => 'Fakultas Sains',
        'slug' => 'fakultas-sains',
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => '22110022',
        'prodi' => 'Fisika',
        'semester' => 4,
        'ipk' => 3.5,
    ]);

    $period = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $registration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 86,
    ]);

    $response = $this->actingAs($adminUniv)
        ->from(route('admin.ranking.index'))
        ->patch(route('admin.ranking.cancel_delegate', $registration));

    $response->assertRedirect(route('admin.ranking.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'stage' => 'fakultas',
    ]);
});

test('admin fakultas can update faculty-scoped guidebook settings', function () {
    $faculty = Faculty::create([
        'name' => 'Fakultas Bahasa',
        'slug' => 'fakultas-bahasa',
    ]);

    $adminFakultas = User::create([
        'name' => 'Admin Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($adminFakultas)
        ->put(route('admin.settings.update'), [
            'guidebook_url' => 'https://example.com/new-guidebook.pdf',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Setting::get('guidebook_url_fakultas_'.$faculty->id))->toBe('https://example.com/new-guidebook.pdf');
});

test('admin fakultas can view settings page', function () {
    $faculty = Faculty::create([
        'name' => 'Fakultas Seni',
        'slug' => 'fakultas-seni',
    ]);

    $adminFakultas = User::create([
        'name' => 'Admin Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($adminFakultas)
        ->get(route('admin.settings.index'));

    $response->assertOk();
});

test('guidebook lookup falls back to university scope when faculty context is missing', function () {
    Setting::setGuidebookUrl(Setting::GUIDEBOOK_SCOPE_UNIVERSITY, 'https://example.com/university-guidebook.pdf');

    $studentWithoutFaculty = User::create([
        'name' => 'Mahasiswa Tanpa Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => null,
        'email_verified_at' => now(),
    ]);

    expect(fn () => Setting::getGuidebookUrlForUser($studentWithoutFaculty))->not->toThrow(InvalidArgumentException::class);
    expect(Setting::getGuidebookUrlForUser($studentWithoutFaculty))->toBe('https://example.com/university-guidebook.pdf');
});

test('admin univ can delegate jury to university via admin juries page', function () {
    Mail::fake();

    $adminUniv = createAdminUniv();

    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik Sipil',
        'slug' => 'fakultas-teknik-sipil',
    ]);

    $juryUser = User::create([
        'name' => 'Juri Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $lecturer = Lecturer::create([
        'user_id' => $juryUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '1988999000',
        'unit_kerja' => 'Teknik Sipil',
        'is_univ_judge' => false,
    ]);

    $response = $this->actingAs($adminUniv)
        ->from(route('admin.juries.index'))
        ->patch(route('admin.juries.toggle_delegation', $lecturer));

    $response->assertRedirect(route('admin.juries.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('lecturers', [
        'id' => $lecturer->id,
        'is_univ_judge' => true,
    ]);
});

test('admin univ gets warning when delegation email fails but stage update persists', function () {
    $adminUniv = createAdminUniv();

    $faculty = Faculty::create([
        'name' => 'Fakultas Teknologi',
        'slug' => 'fakultas-teknologi',
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Delegasi',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => '22110123',
        'prodi' => 'Teknik Informatika',
        'semester' => 7,
        'ipk' => 3.9,
    ]);

    $period = PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(2)->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $registration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 92,
    ]);

    $notificationServiceMock = \Mockery::mock(UniversityDelegationNotificationService::class);
    $notificationServiceMock
        ->shouldReceive('sendParticipantDelegatedToUniversity')
        ->once()
        ->andThrow(new \RuntimeException('Mail transport unavailable'));

    $this->app->instance(UniversityDelegationNotificationService::class, $notificationServiceMock);

    $response = $this->actingAs($adminUniv)
        ->from(route('admin.ranking.index'))
        ->post(route('admin.ranking.delegate', $registration));

    $response->assertRedirect(route('admin.ranking.index'));
    $response->assertSessionHas('warning');

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'stage' => 'universitas',
    ]);
});
