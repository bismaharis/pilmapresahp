<?php

use App\Models\Assessment;
use App\Models\Criteria;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;

function createTransparencyNotesFilterContext(): array
{
    $faculty = Faculty::create([
        'name' => 'Fakultas Transparansi',
        'slug' => 'fakultas-transparansi',
    ]);

    $adminUser = User::create([
        'name' => 'Admin Transparansi',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Transparansi',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => '22440011',
        'prodi' => 'Informatika',
        'semester' => 6,
        'ipk' => 3.85,
    ]);

    $lecturerUser = User::create([
        'name' => 'Juri Transparansi',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $lecturer = Lecturer::create([
        'user_id' => $lecturerUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '19881111999',
        'unit_kerja' => 'Sistem Informasi',
        'is_univ_judge' => false,
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
        'status' => 'submitted',
        'file_gk' => 'files/gk/transparansi.pdf',
        'file_transkrip' => 'files/transkrip/transparansi.pdf',
        'total_score_fakultas' => 87.5,
    ]);

    $gkRoot = Criteria::create([
        'name' => 'Gagasan Kreatif',
        'type' => 'gk',
        'weight' => 0.65,
        'max_score' => 100,
        'parent_id' => null,
        'cr_value' => 0,
        'cr_status' => 'consistent',
    ]);

    $gkLeaf = Criteria::create([
        'name' => 'Substansi',
        'type' => 'gk',
        'weight' => 1,
        'max_score' => 100,
        'parent_id' => $gkRoot->id,
        'cr_value' => 0,
        'cr_status' => 'consistent',
    ]);

    $cuRoot = Criteria::create([
        'name' => 'Capaian Unggulan',
        'type' => 'cu',
        'weight' => 0.35,
        'max_score' => 500,
        'parent_id' => null,
        'cr_value' => 0,
        'cr_status' => 'consistent',
    ]);

    $cuCategory = Criteria::create([
        'name' => 'Kompetisi',
        'type' => 'cu',
        'weight' => 1,
        'max_score' => 50,
        'parent_id' => $cuRoot->id,
        'cr_value' => 0,
        'cr_status' => 'consistent',
    ]);

    Assessment::create([
        'registration_id' => $registration->id,
        'lecturer_id' => $lecturer->id,
        'criteria_id' => $cuCategory->id,
        'score' => 40,
        'notes' => json_encode([
            'achievement_scores' => [
                '25' => 40,
            ],
        ]),
    ]);

    Assessment::create([
        'registration_id' => $registration->id,
        'lecturer_id' => $lecturer->id,
        'criteria_id' => $gkLeaf->id,
        'score' => 88,
        'notes' => 'Komentar juri: argumentasi kuat dan struktur naskah rapi.',
    ]);

    return compact('adminUser', 'registration');
}

test('transparency show hanya menampilkan komentar juri tanpa payload achievement_scores json', function () {
    $context = createTransparencyNotesFilterContext();

    $response = $this->actingAs($context['adminUser'])
        ->get(route('transparency.show', ['id' => $context['registration']->id, 'stage' => 'fakultas']));

    $response->assertOk();
    $response->assertSee('Komentar juri: argumentasi kuat dan struktur naskah rapi.');
    $response->assertDontSee('achievement_scores');
});
