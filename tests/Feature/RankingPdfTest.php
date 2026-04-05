<?php

use App\Models\Faculty;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exports faculty ranking pdf and includes student name in view rendering', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $faculty = Faculty::create(['name' => 'Teknik', 'slug' => 'teknik']);

    $studentUser = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'role' => 'mahasiswa',
    ]);

    $student = Student::create([
        'user_id' => $studentUser->id,
        'faculty_id' => $faculty->id,
        'nim' => '12345678',
        'prodi' => 'Teknik Informatika',
        'semester' => 5,
        'ipk' => 3.75,
    ]);

    $period = \App\Models\PilmapresPeriod::create([
        'faculty_id' => $faculty->id,
        'year' => 2026,
        'is_active' => true,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
    ]);

    $registration = Registration::create([
        'period_id' => $period->id,
        'student_id' => $student->id,
        'stage' => 'fakultas',
        'status' => 'approved',
        'total_score_fakultas' => 88.50,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.ranking.pdf', ['stage' => 'fakultas', 'faculty_id' => $faculty->id]));
    $response->assertStatus(200);
    $response->assertHeader('content-disposition');

    $rendered = view('admin.ranking.pdf', [
        'rankings' => Registration::with(['student.user'])->where('id', $registration->id)->get(),
        'stage' => 'fakultas',
        'scoreColumn' => 'total_score_fakultas',
        'facultyNameTitle' => ' - TEKNIK',
    ])->render();

    expect($rendered)->toContain('Budi Santoso');
});
