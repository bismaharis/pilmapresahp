<?php

use App\Models\Faculty;
use App\Models\User;

function createSuperAdmin(): User
{
    return User::create([
        'name' => 'Super Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
}

test('super admin creates participant with faculty_id synced to users and students tables', function () {
    $superAdmin = createSuperAdmin();
    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $response = $this->actingAs($superAdmin)->post(route('admin.participants.store'), [
        'name' => 'Peserta Baru',
        'email' => 'peserta@example.com',
        'password' => 'password123',
        'nim' => '2026123456',
        'prodi' => 'Informatika',
        'faculty_id' => $faculty->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'peserta@example.com',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
    ]);

    $this->assertDatabaseHas('students', [
        'nim' => '2026123456',
        'faculty_id' => $faculty->id,
    ]);
});

test('super admin creates jury with faculty_id synced to users and lecturers tables', function () {
    $superAdmin = createSuperAdmin();
    $faculty = Faculty::create([
        'name' => 'Fakultas Ekonomi',
        'slug' => 'fakultas-ekonomi',
    ]);

    $response = $this->actingAs($superAdmin)->post(route('admin.juries.store'), [
        'name' => 'Dosen Juri',
        'email' => 'juri@example.com',
        'password' => 'password123',
        'nip' => '1987001',
        'faculty_id' => $faculty->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'juri@example.com',
        'role' => 'dosen',
        'faculty_id' => $faculty->id,
    ]);

    $juriUser = User::where('email', 'juri@example.com')->firstOrFail();

    $this->assertDatabaseHas('lecturers', [
        'user_id' => $juriUser->id,
        'faculty_id' => $faculty->id,
    ]);
});

test('admin fakultas must have faculty_id while admin universitas can be null', function () {
    $superAdmin = createSuperAdmin();
    $faculty = Faculty::create([
        'name' => 'Fakultas Hukum',
        'slug' => 'fakultas-hukum',
    ]);

    $failedResponse = $this->actingAs($superAdmin)->from(route('superadmin.committees.index'))
        ->post(route('superadmin.committees.store'), [
            'name' => 'Admin Fakultas Tanpa Fakultas',
            'email' => 'adminfak-null@example.com',
            'password' => 'password123',
            'role' => 'admin_fakultas',
            'faculty_id' => '',
        ]);

    $failedResponse->assertRedirect(route('superadmin.committees.index'));
    $failedResponse->assertSessionHasErrors('faculty_id');

    $successFakultasResponse = $this->actingAs($superAdmin)->post(route('superadmin.committees.store'), [
        'name' => 'Admin Fakultas Valid',
        'email' => 'adminfak@example.com',
        'password' => 'password123',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
    ]);

    $successFakultasResponse->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'adminfak@example.com',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
    ]);

    $successUnivResponse = $this->actingAs($superAdmin)->post(route('superadmin.committees.store'), [
        'name' => 'Admin Univ',
        'email' => 'adminuniv@example.com',
        'password' => 'password123',
        'role' => 'admin_univ',
        'faculty_id' => '',
    ]);

    $successUnivResponse->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'adminuniv@example.com',
        'role' => 'admin_univ',
        'faculty_id' => null,
    ]);
});
