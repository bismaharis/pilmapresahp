<?php

use App\Models\Achievement;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\PilmapresPeriod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;

function createJuriAssessmentContext(): array
{
    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $studentUser = User::create([
        'name' => 'Mahasiswa Dinilai',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => '220001111',
        'prodi' => 'Informatika',
        'semester' => 6,
        'ipk' => 3.8,
    ]);

    $juriUser = User::create([
        'name' => 'Dosen Juri',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    Lecturer::create([
        'user_id' => $juriUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '19870011',
        'unit_kerja' => 'Sistem Informasi',
        'is_univ_judge' => false,
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
        'status' => 'submitted',
        'file_gk' => 'files/gk/gk.pdf',
        'file_transkrip' => 'files/transkrip/transkrip.pdf',
        'file_poster_diri' => 'files/posters/poster-diri.pdf',
    ]);

    Achievement::create([
        'registration_id' => $registration->id,
        'name' => 'Juara Kompetisi',
        'capaian' => 'Juara 1',
        'category' => 'Kompetisi',
        'organizer' => 'Kampus',
        'year' => 2025,
        'type' => 'individu',
        'jumlah_peserta' => 100,
        'jumlah_penghargaan' => 3,
        'level' => 'nasional',
        'file_proof' => 'files/proof/bukti.pdf',
    ]);

    return compact('juriUser', 'registration');
}

test('halaman edit penilaian juri menampilkan popup viewer dan tautan download sertifikat', function () {
    $context = createJuriAssessmentContext();

    $response = $this->actingAs($context['juriUser'])
        ->get(route('juri.assessments.edit', $context['registration']));

    $response->assertOk();
    $response->assertSee('id="file-viewer-modal"', false);
    $response->assertSee('id="file-viewer-frame"', false);
    $response->assertSee('id="file-viewer-open-new-tab"', false);
    $response->assertSee('max-w-3xl', false);
    $response->assertSee('openFileViewer(', false);
    $response->assertSee('/storage/files/transkrip/transkrip.pdf', false);
    $response->assertSee('Buka Transkrip', false);
    $response->assertSee('/storage/files/posters/poster-diri.pdf', false);
    $response->assertSee('Lihat Poster Diri', false);
    $response->assertSee('/storage/files/proof/bukti.pdf', false);
    $response->assertSee('Download Sertifikat/Bukti', false);
    $response->assertSee('download', false);
    $response->assertDontSee('Lihat Sertifikat/Bukti');
});
