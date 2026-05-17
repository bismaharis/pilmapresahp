<?php

use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;

function createAdminFakultas(Faculty $faculty): User
{
    return User::create([
        'name' => 'Admin Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);
}

test('admin fakultas cannot update participant from another faculty', function () {
    $facultyA = Faculty::create(['name' => 'Fakultas A', 'slug' => 'fakultas-a']);
    $facultyB = Faculty::create(['name' => 'Fakultas B', 'slug' => 'fakultas-b']);

    $adminFakultas = createAdminFakultas($facultyA);

    $participant = User::create([
        'name' => 'Peserta Fakultas B',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $facultyB->id,
        'email_verified_at' => now(),
    ]);

    Student::create([
        'user_id' => $participant->id,
        'faculty_id' => $facultyB->id,
        'nim' => '22030099',
        'prodi' => 'Teknik Mesin',
        'semester' => 4,
        'ipk' => 3.2,
    ]);

    $response = $this->actingAs($adminFakultas)->put(route('admin.participants.update', $participant), [
        'name' => 'Nama Diserang',
        'email' => $participant->email,
        'nim' => '22030099',
        'prodi' => 'Teknik Mesin',
        'faculty_id' => $facultyA->id,
    ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $participant->id,
        'name' => 'Peserta Fakultas B',
        'faculty_id' => $facultyB->id,
    ]);
});

test('admin fakultas cannot delete participant from another faculty', function () {
    $facultyA = Faculty::create(['name' => 'Fakultas C', 'slug' => 'fakultas-c']);
    $facultyB = Faculty::create(['name' => 'Fakultas D', 'slug' => 'fakultas-d']);

    $adminFakultas = createAdminFakultas($facultyA);

    $participant = User::create([
        'name' => 'Peserta Fakultas D',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $facultyB->id,
        'email_verified_at' => now(),
    ]);

    Student::create([
        'user_id' => $participant->id,
        'faculty_id' => $facultyB->id,
        'nim' => '22030111',
        'prodi' => 'Hukum',
        'semester' => 5,
        'ipk' => 3.4,
    ]);

    $response = $this->actingAs($adminFakultas)->delete(route('admin.participants.destroy', $participant));

    $response->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $participant->id,
        'role' => 'mahasiswa',
    ]);
});

test('admin fakultas cannot access jury edit page for non-dosen user', function () {
    $faculty = Faculty::create(['name' => 'Fakultas E', 'slug' => 'fakultas-e']);
    $adminFakultas = createAdminFakultas($faculty);

    $participant = User::create([
        'name' => 'Peserta Salah Endpoint',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($adminFakultas)->get(route('admin.juries.edit', $participant));

    $response->assertNotFound();
});

test('admin fakultas cannot edit jury from another faculty', function () {
    $facultyA = Faculty::create(['name' => 'Fakultas F', 'slug' => 'fakultas-f']);
    $facultyB = Faculty::create(['name' => 'Fakultas G', 'slug' => 'fakultas-g']);

    $adminFakultas = createAdminFakultas($facultyA);

    $jury = User::create([
        'name' => 'Juri Fakultas G',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'faculty_id' => $facultyB->id,
        'email_verified_at' => now(),
    ]);

    Lecturer::create([
        'user_id' => $jury->id,
        'faculty_id' => $facultyB->id,
        'nip' => '19000012',
        'unit_kerja' => 'Fakultas G',
        'is_univ_judge' => false,
    ]);

    $response = $this->actingAs($adminFakultas)->get(route('admin.juries.edit', $jury));

    $response->assertForbidden();
});
