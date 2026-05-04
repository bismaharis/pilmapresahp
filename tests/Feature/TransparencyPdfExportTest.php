<?php

use App\Models\Faculty;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

function createSuperAdminForTransparency(): User
{
    return User::create([
        'name' => 'Super Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
}

function createStudentForTransparency(Faculty $faculty, string $name, string $nim): Student
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
        'ipk' => 3.7,
    ]);
}

function createPeriodForTransparency(?int $facultyId = null): PilmapresPeriod
{
    return PilmapresPeriod::create([
        'faculty_id' => $facultyId,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now()->subDays(5)->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
    ]);
}

test('super admin export pdf mengikuti filter fakultas yang dipilih', function () {
    $superAdmin = createSuperAdminForTransparency();

    $facultyTeknik = Faculty::create(['name' => 'Fakultas Teknik', 'slug' => 'ft']);
    $facultyHukum = Faculty::create(['name' => 'Fakultas Hukum', 'slug' => 'fh']);

    $studentTeknik = createStudentForTransparency($facultyTeknik, 'Peserta Teknik', '22010001');
    $studentHukum = createStudentForTransparency($facultyHukum, 'Peserta Hukum', '22010002');

    $period = createPeriodForTransparency();

    Registration::create([
        'period_id' => $period->id,
        'student_id' => $studentTeknik->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 90,
        'file_gk' => 'files/gk/teknik.pdf',
        'file_transkrip' => 'files/transkrip/teknik.pdf',
    ]);

    Registration::create([
        'period_id' => $period->id,
        'student_id' => $studentHukum->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 92,
        'file_gk' => 'files/gk/hukum.pdf',
        'file_transkrip' => 'files/transkrip/hukum.pdf',
    ]);

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(function (string $view, array $data): bool {
            if ($view !== 'transparency.pdf') {
                return false;
            }

            $names = $data['rankings']->map(fn ($item) => $item->student->user->name)->all();

            return in_array('Peserta Teknik', $names, true)
                && ! in_array('Peserta Hukum', $names, true)
                && $data['stage'] === 'fakultas';
        })
        ->andReturnSelf();

    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(response('PDF-OK', 200));

    $response = $this->actingAs($superAdmin)->get(route('transparency.pdf', [
        'stage' => 'fakultas',
        'faculty_id' => $facultyTeknik->id,
    ]));

    $response->assertOk();
    $response->assertSee('PDF-OK');
});

test('super admin export pdf stage universitas hanya memuat peserta universitas', function () {
    $superAdmin = createSuperAdminForTransparency();

    $facultyTeknik = Faculty::create(['name' => 'Fakultas Teknik', 'slug' => 'ft2']);

    $studentUniversitas = createStudentForTransparency($facultyTeknik, 'Peserta Universitas', '22020001');
    $studentFakultas = createStudentForTransparency($facultyTeknik, 'Peserta Fakultas', '22020002');

    $period = createPeriodForTransparency();

    Registration::create([
        'period_id' => $period->id,
        'student_id' => $studentUniversitas->id,
        'stage' => 'universitas',
        'status' => 'finalist',
        'total_score_univ' => 88,
        'file_gk' => 'files/gk/univ.pdf',
        'file_transkrip' => 'files/transkrip/univ.pdf',
    ]);

    Registration::create([
        'period_id' => $period->id,
        'student_id' => $studentFakultas->id,
        'stage' => 'fakultas',
        'status' => 'verified',
        'total_score_fakultas' => 95,
        'file_gk' => 'files/gk/fak.pdf',
        'file_transkrip' => 'files/transkrip/fak.pdf',
    ]);

    Pdf::shouldReceive('loadView')
        ->once()
        ->withArgs(function (string $view, array $data): bool {
            if ($view !== 'transparency.pdf') {
                return false;
            }

            $names = $data['rankings']->map(fn ($item) => $item->student->user->name)->all();

            return in_array('Peserta Universitas', $names, true)
                && ! in_array('Peserta Fakultas', $names, true)
                && $data['stage'] === 'universitas';
        })
        ->andReturnSelf();

    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('download')->once()->andReturn(response('PDF-OK', 200));

    $response = $this->actingAs($superAdmin)
        ->get(route('transparency.pdf', ['stage' => 'universitas']));

    $response->assertOk();
    $response->assertSee('PDF-OK');
});
