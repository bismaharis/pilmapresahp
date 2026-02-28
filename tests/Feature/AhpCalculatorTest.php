<?php

use App\Models\Achievement;
use App\Models\Assessment;
use App\Models\Criteria;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Services\AhpCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

// RefreshDatabase memastikan database di-reset setiap kali test dijalankan 
// (tidak akan merusak database utama project Anda)
uses(RefreshDatabase::class);

it('menghitung nilai akhir AHP dengan sangat akurat', function () {
    
    // ---------------------------------------------------------
    // 1. SETUP DATA KRITERIA (Simulasi Sederhana)
    // ---------------------------------------------------------
    // Kriteria Induk GK (Bobot 60%)
    $gkRoot = Criteria::create(['name' => 'Gagasan Kreatif', 'type' => 'gk', 'weight' => 0.6, 'max_score' => 0]);
    // Anak GK: Substansi (Bobot 100% dari GK = Global 60%), Nilai Maksimal form: 100
    $gkSub = Criteria::create(['name' => 'Substansi', 'type' => 'gk', 'weight' => 1.0, 'max_score' => 100, 'parent_id' => $gkRoot->id]);

    // Kriteria Induk CU (Bobot 40%)
    $cuRoot = Criteria::create(['name' => 'Capaian Unggulan', 'type' => 'cu', 'weight' => 0.4, 'max_score' => 0]);
    // Anak CU: Kompetisi (Bobot 100% dari CU = Global 40%)
    $cuSub = Criteria::create(['name' => 'Kompetisi', 'type' => 'cu', 'weight' => 1.0, 'max_score' => 0, 'parent_id' => $cuRoot->id]);

    // ---------------------------------------------------------
    // 2. SETUP USER & PENDAFTARAN
    // ---------------------------------------------------------
    $faculty = Faculty::create(['name' => 'Fakultas Teknik', 'slug' => 'teknik']);
    
    // Pastikan user diberi role dan faculty_id
    $user = User::factory()->create(['role' => 'mahasiswa', 'faculty_id' => $faculty->id]);
    
    DB::table('pilmapres_periods')->insert([
        'id' => 1,
        'year' => '2026',
        'is_active' => true,
        'start_date' => now(),
        'end_date' => now()->addMonths(3),
    ]);
    $faculty = Faculty::firstOrCreate(
        ['slug' => 'teknik'], 
        ['name' => 'Fakultas Teknik']
    );

    $student = Student::create([
        'user_id' => $user->id, 
        'faculty_id' => $faculty->id, 
        'nim' => 'A1B2C3', 
        'prodi' => 'Teknik Informatika',
        'semester' => 6,
        'ipk' => 3.85
    ]);
    
    $registration = Registration::create([
        'student_id' => $student->id,
        'period_id' => 1, // Sekarang ini akan valid!
        'stage' => 'fakultas',
        'status' => 'submitted'
    ]);

    // ---------------------------------------------------------
    // 3. SETUP CAPAIAN UNGGULAN (CU)
    // ---------------------------------------------------------
    // Mahasiswa input 1 Prestasi Tingkat Nasional (Berdasarkan rumus, Nasional = 40 poin mentah)
    Achievement::create([
        'registration_id' => $registration->id,
        'name' => 'Juara 1 Web Design',
        // 'capaian' must be provided due to NOT NULL constraint
        'capaian' => 'Juara 1 Web Design',
        'category' => 'Kompetisi',
        'level' => 'Nasional',
        'year' => 2025,
        'type' => 'Individu',
        'organizer' => 'Kemenristek',
        'file_proof' => 'dummy.pdf'
    ]);

    // ---------------------------------------------------------
    // 4. SETUP PENILAIAN JURI (GK)
    // ---------------------------------------------------------
    // Juri memberikan nilai 80 (dari maksimal 100) untuk kriteria Substansi
    $juriUser = User::factory()->create(['role' => 'dosen', 'faculty_id' => $faculty->id]);
    $juri = Lecturer::create([
        'user_id' => $juriUser->id, 
        'faculty_id' => $faculty->id, 
        'nip' => '12345', 
        'unit_kerja' => 'Teknik Informatika'
    ]);

    // create a juror assessment for the GK sub-criteria with score 80
    Assessment::create([
        'registration_id' => $registration->id,
        'lecturer_id' => $juri->id,
        'criteria_id' => $gkSub->id,
        'score' => 80,
    ]);

    // ---------------------------------------------------------
    // 5. EKSEKUSI ENGINE AHP
    // ---------------------------------------------------------
    $service = new AhpCalculatorService();
    $finalScore = $service->calculateFinalScore($registration);

    // ---------------------------------------------------------
    // 6. ASSERTION (PEMBUKTIAN MATEMATIKA)
    // ---------------------------------------------------------
    /*
        PERHITUNGAN MANUAL:
        - CU Score: Nilai Mentah = 40. Normalisasi = (40 / 200) * 100 = 20. 
          Total CU = 20 * 0.4 (Bobot Global) = 8.
          
        - GK Score: Nilai Mentah = 80. Normalisasi = (80 / 100) * 100 = 80.
          Total GK = 80 * 0.6 (Bobot Global) = 48.
          
        - TOTAL AKHIR = 48 + 8 = 56.
    */
    
    // A. Pastikan nilai return service adalah 56.0
    expect($finalScore)->toEqual(56.0);
    
    // B. Pastikan nilai 56.0 benar-benar tersimpan di database kolom total_score_fakultas
    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'total_score_fakultas' => 56.0
    ]);
});