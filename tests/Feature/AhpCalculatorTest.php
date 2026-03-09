<?php

use App\Models\Criteria;
use App\Models\Registration;
use App\Models\Assessment;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\User;
use App\Services\AhpCalculatorService;

beforeEach(function () {
    // ensure active period exists (some controllers rely on it)
    if (!\DB::table('pilmapres_periods')->where('is_active', true)->exists()) {
        \DB::table('pilmapres_periods')->insert([
            'year' => now()->year,
            'is_active' => true,
            'start_date' => now()->subWeek(),
            'end_date' => now()->addWeek(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
});

it('calculates CU score using dynamic max and ignores subcriteria weights', function () {
    $user = User::factory()->create();
    $faculty = Faculty::create(['name' => 'Fakultas', 'slug' => 'fakultas']);
    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '123',
        'prodi' => 'X',
        'semester' => 1,
        'ipk' => 3.0,
    ]);

    $registration = Registration::create([
        'period_id' => 1,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'draft',
    ]);

    $cuRoot = Criteria::create(['name' => 'CU Root', 'type' => 'cu', 'weight' => 0.35, 'max_score' => 0]);
    $leaf1 = Criteria::create(['name' => 'cu1', 'type' => 'cu', 'weight' => 0, 'max_score' => 200, 'parent_id' => $cuRoot->id]);
    $leaf2 = Criteria::create(['name' => 'cu2', 'type' => 'cu', 'weight' => 0, 'max_score' => 200, 'parent_id' => $cuRoot->id]);

    // create a lecturer so FK constraint is satisfied
    $lecturerUser = User::factory()->create();
    $lecturer = \App\Models\Lecturer::create([
        'user_id' => $lecturerUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '123',
        'unit_kerja' => 'Test',
        'is_univ_judge' => false,
    ]);

    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf1->id, 'score' => 40]);
    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf2->id, 'score' => 50]);

    $service = new AhpCalculatorService();
    $score   = $service->calculateFinalScore($registration);

    // totalRaw 90 -> (90/500)*100*0.35 = 6.3
    expect(round($score, 4))->toBe(6.3);
});

it('falls back to 500 when no max scores are defined for CU', function () {
    $user = User::factory()->create();
    $faculty = Faculty::create(['name' => 'Fakultas 2', 'slug' => 'fakultas2']);
    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '456',
        'prodi' => 'Y',
        'semester' => 2,
        'ipk' => 3.5,
    ]);

    $registration = Registration::create([
        'period_id' => 1,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'draft',
    ]);

    $cuRoot = Criteria::create(['name' => 'CU Root 2', 'type' => 'cu', 'weight' => 0.35, 'max_score' => 0]);
    $leaf1 = Criteria::create(['name' => 'cu1', 'type' => 'cu', 'weight' => 0, 'max_score' => 200, 'parent_id' => $cuRoot->id]);
    $leaf2 = Criteria::create(['name' => 'cu2', 'type' => 'cu', 'weight' => 0, 'max_score' => 200, 'parent_id' => $cuRoot->id]);

    // create lecturer for FK
    $lecturerUser = User::factory()->create();
    $lecturer = \App\Models\Lecturer::create([
        'user_id' => $lecturerUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '456',
        'unit_kerja' => 'Test',
        'is_univ_judge' => false,
    ]);

    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf1->id, 'score' => 40]);
    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf2->id, 'score' => 50]);

    $service = new AhpCalculatorService();
    $score   = $service->calculateFinalScore($registration);

    // totalRaw 90 -> (90/500)*100*0.35 = 6.3
    expect(round($score, 4))->toBe(6.3);
});

it('returns only juri score when no CU assessments exist', function () {
    $user = User::factory()->create();
    $faculty = Faculty::create(['name' => 'Fakultas 3', 'slug' => 'fakultas3']);
    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '789',
        'prodi' => 'Z',
        'semester' => 3,
        'ipk' => 3.2,
    ]);

    $registration = Registration::create([
        'period_id' => 1,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'draft',
    ]);

    // setup a simple GK branch
    $gkRoot = Criteria::create(['name' => 'GK Root', 'type' => 'gk', 'weight' => 1, 'max_score' => 0]);
    $leaf = Criteria::create(['name' => 'gk1', 'type' => 'gk', 'weight' => 1, 'max_score' => 100, 'parent_id' => $gkRoot->id]);

    $lecturerUser = User::factory()->create();
    $lecturer = \App\Models\Lecturer::create([
        'user_id' => $lecturerUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '789',
        'unit_kerja' => 'Test',
        'is_univ_judge' => false,
    ]);

    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf->id, 'score' => 80]);

    $service = new AhpCalculatorService();
    $score   = $service->calculateFinalScore($registration);

    // juri only: (80/100)*100*1 = 80
    expect(round($score, 4))->toEqual(80);
});

it('caps CU totalRaw at 500 even if jurors input more', function () {
    $user = User::factory()->create();
    $faculty = Faculty::create(['name' => 'Fakultas Cap', 'slug' => 'cap']);
    $student = Student::create([
        'user_id' => $user->id,
        'faculty_id' => $faculty->id,
        'nim' => '000',
        'prodi' => 'Z',
        'semester' => 4,
        'ipk' => 3.0,
    ]);

    $registration = Registration::create([
        'period_id' => 1,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'draft',
    ]);

    $cuRoot = Criteria::create(['name' => 'CU Cap', 'type' => 'cu', 'weight' => 0.35, 'max_score' => 0]);
    $leaf1 = Criteria::create(['name' => 'cu1', 'type' => 'cu', 'weight' => 0, 'max_score' => 200, 'parent_id' => $cuRoot->id]);
    $leaf2 = Criteria::create(['name' => 'cu2', 'type' => 'cu', 'weight' => 0, 'max_score' => 200, 'parent_id' => $cuRoot->id]);

    $lecturerUser = User::factory()->create();
    $lecturer = \App\Models\Lecturer::create([
        'user_id' => $lecturerUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '999',
        'unit_kerja' => 'Test',
        'is_univ_judge' => false,
    ]);

    // enter a huge sum > 500
    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf1->id, 'score' => 400]);
    Assessment::create(['registration_id' => $registration->id, 'lecturer_id' => $lecturer->id, 'criteria_id' => $leaf2->id, 'score' => 200]);

    $service = new AhpCalculatorService();
    $score   = $service->calculateFinalScore($registration);

    // totalRaw capped at 500 -> (500/500)*100*0.35 = 35
    expect(round($score, 4))->toEqual(35);
});
