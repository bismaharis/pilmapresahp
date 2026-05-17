<?php

use App\Models\Achievement;
use App\Models\Criteria;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;

function createJurySecurityContext(string $registrationStatus = 'submitted'): array
{
    $faculty = Faculty::create([
        'name' => 'Fakultas Keamanan',
        'slug' => 'fakultas-keamanan',
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Uji',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => fake()->unique()->numerify('22######'),
        'prodi' => 'Teknik Informatika',
        'semester' => 6,
        'ipk' => 3.8,
    ]);

    $juryUser = User::create([
        'name' => 'Dosen Penilai',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $lecturer = Lecturer::create([
        'user_id' => $juryUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '1988112233',
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
        'status' => $registrationStatus,
        'file_gk' => 'files/gk/doc.pdf',
        'file_transkrip' => 'files/transkrip/doc.pdf',
    ]);

    $otherStudentUser = User::create([
        'name' => 'Mahasiswa Lain',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $otherStudent = Student::create([
        'user_id' => $otherStudentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => fake()->unique()->numerify('23######'),
        'prodi' => 'Teknik Informatika',
        'semester' => 4,
        'ipk' => 3.4,
    ]);

    $otherRegistration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $otherStudent->id,
        'stage' => 'fakultas',
        'status' => 'submitted',
        'file_gk' => 'files/gk/other.pdf',
        'file_transkrip' => 'files/transkrip/other.pdf',
    ]);

    $achievement = Achievement::create([
        'registration_id' => $registration->id,
        'name' => 'Prestasi Valid',
        'capaian' => 'Juara 1',
        'category' => 'Kompetisi',
        'organizer' => 'Kemendikbud',
        'year' => 2025,
        'type' => 'individu',
        'jumlah_peserta' => 100,
        'jumlah_penghargaan' => 3,
        'level' => 'nasional',
        'file_proof' => 'files/proof/valid.pdf',
    ]);

    $foreignAchievement = Achievement::create([
        'registration_id' => $otherRegistration->id,
        'name' => 'Prestasi Asing',
        'capaian' => 'Juara 2',
        'category' => 'Kompetisi',
        'organizer' => 'Kampus',
        'year' => 2024,
        'type' => 'individu',
        'jumlah_peserta' => 50,
        'jumlah_penghargaan' => 5,
        'level' => 'regional',
        'file_proof' => 'files/proof/foreign.pdf',
    ]);

    $gkRoot = Criteria::create([
        'name' => 'Gagasan Kreatif',
        'type' => 'gk',
        'weight' => 0.5,
        'max_score' => 100,
        'parent_id' => null,
        'cr_value' => 0,
        'cr_status' => 'consistent',
    ]);

    $gkLeaf = Criteria::create([
        'name' => 'Sub GK',
        'type' => 'gk',
        'weight' => 1,
        'max_score' => 10,
        'parent_id' => $gkRoot->id,
        'cr_value' => 0,
        'cr_status' => 'consistent',
    ]);

    $cuRoot = Criteria::create([
        'name' => 'Capaian Unggulan',
        'type' => 'cu',
        'weight' => 0.5,
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

    return compact(
        'juryUser',
        'lecturer',
        'registration',
        'achievement',
        'foreignAchievement',
        'gkLeaf',
        'cuCategory'
    );
}

test('jury cannot assess participant with draft status even if files exist', function () {
    $context = createJurySecurityContext('draft');

    $response = $this->actingAs($context['juryUser'])
        ->put(route('juri.assessments.update', $context['registration']), [
            'scores' => [
                $context['gkLeaf']->id => 8,
            ],
            'achievement_scores' => [
                $context['achievement']->id => 20,
            ],
        ]);

    $response->assertForbidden();
});

test('jury cannot submit score above criteria max score', function () {
    $context = createJurySecurityContext();

    $response = $this->actingAs($context['juryUser'])
        ->from(route('juri.assessments.edit', $context['registration']))
        ->put(route('juri.assessments.update', $context['registration']), [
            'scores' => [
                $context['gkLeaf']->id => 11,
            ],
            'achievement_scores' => [
                $context['achievement']->id => 20,
            ],
        ]);

    $response->assertRedirect(route('juri.assessments.edit', $context['registration']));
    $response->assertSessionHasErrors("scores.{$context['gkLeaf']->id}");
});

test('jury cannot send achievement score ids from other registration', function () {
    $context = createJurySecurityContext();

    $response = $this->actingAs($context['juryUser'])
        ->from(route('juri.assessments.edit', $context['registration']))
        ->put(route('juri.assessments.update', $context['registration']), [
            'scores' => [
                $context['gkLeaf']->id => 9,
            ],
            'achievement_scores' => [
                $context['achievement']->id => 20,
                $context['foreignAchievement']->id => 30,
            ],
        ]);

    $response->assertRedirect(route('juri.assessments.edit', $context['registration']));
    $response->assertSessionHasErrors('achievement_scores');
});

test('jury CU per-achievement scores are persisted and restored on edit', function () {
    $context = createJurySecurityContext();

    $submitResponse = $this->actingAs($context['juryUser'])
        ->put(route('juri.assessments.update', $context['registration']), [
            'scores' => [
                $context['gkLeaf']->id => 8.5,
            ],
            'achievement_scores' => [
                $context['achievement']->id => 27.5,
            ],
        ]);

    $submitResponse->assertRedirect(route('juri.assessments.index'));

    $editResponse = $this->actingAs($context['juryUser'])
        ->get(route('juri.assessments.edit', $context['registration']));

    $editResponse->assertOk();
    $editResponse->assertSee('name="achievement_scores['.$context['achievement']->id.']"', false);
    $editResponse->assertSee('value="27.5"', false);
});

test('faculty jury index menampilkan registration periode aktif dan jumlah CU terbaru', function () {
    $faculty = Faculty::create([
        'name' => 'Fakultas Filter Aktif',
        'slug' => 'fakultas-filter-aktif',
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Riwayat dan Aktif',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => fake()->unique()->numerify('24######'),
        'prodi' => 'Teknik Industri',
        'semester' => 6,
        'ipk' => 3.7,
    ]);

    $juryUser = User::create([
        'name' => 'Dosen Filter Aktif',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    Lecturer::create([
        'user_id' => $juryUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '199001011111',
        'unit_kerja' => 'Teknik Mesin',
        'is_univ_judge' => false,
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

    $oldRegistration = Registration::create([
        'period_id' => $oldPeriod->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'submitted',
        'file_gk' => 'files/gk/lama.pdf',
        'file_transkrip' => 'files/transkrip/lama.pdf',
    ]);

    $activeRegistration = Registration::create([
        'period_id' => $activePeriod->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'submitted',
        'file_gk' => 'files/gk/baru.pdf',
        'file_transkrip' => 'files/transkrip/baru.pdf',
    ]);

    Achievement::create([
        'registration_id' => $activeRegistration->id,
        'name' => 'CU Aktif',
        'capaian' => 'Juara 1',
        'category' => 'Kompetisi',
        'organizer' => 'Kemendikbud',
        'year' => 2026,
        'type' => 'individu',
        'jumlah_peserta' => 100,
        'jumlah_penghargaan' => 1,
        'level' => 'nasional',
        'file_proof' => 'proofs/aktif.pdf',
    ]);

    $response = $this->actingAs($juryUser)
        ->get(route('juri.assessments.index'));

    $response->assertOk();
    $response->assertSee('/storage/files/gk/baru.pdf');
    $response->assertSee('1 Sertifikat');
    $response->assertDontSee('/storage/files/gk/lama.pdf');
});
