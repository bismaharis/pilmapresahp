<?php

use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\User;

function createVerifiedSuperAdmin(): User
{
    return User::create([
        'name' => 'Super Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
}

test('super admin cannot open committee edit page for non-committee roles', function () {
    $superAdmin = createVerifiedSuperAdmin();

    $studentUser = User::create([
        'name' => 'Mahasiswa Uji',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'mahasiswa',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('superadmin.committees.edit', $studentUser));

    $response->assertNotFound();
});

test('super admin cannot delete non-committee roles from committee management route', function () {
    $superAdmin = createVerifiedSuperAdmin();

    $juryUser = User::create([
        'name' => 'Dosen Uji',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'dosen',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($superAdmin)
        ->delete(route('superadmin.committees.destroy', $juryUser));

    $response->assertNotFound();

    $this->assertDatabaseHas('users', [
        'id' => $juryUser->id,
        'role' => 'dosen',
    ]);
});

test('super admin cannot be targeted by committee management deletion route', function () {
    $superAdmin = createVerifiedSuperAdmin();

    $response = $this->actingAs($superAdmin)
        ->delete(route('superadmin.committees.destroy', $superAdmin));

    $response->assertNotFound();

    $this->assertDatabaseHas('users', [
        'id' => $superAdmin->id,
        'role' => 'super_admin',
    ]);
});

test('delegation toggle rejects lecturer profiles that are not dosen users', function () {
    $superAdmin = createVerifiedSuperAdmin();

    $faculty = Faculty::create([
        'name' => 'Fakultas Kedokteran',
        'slug' => 'fakultas-kedokteran',
    ]);

    $adminUser = User::create([
        'name' => 'Admin Fakultas',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $lecturer = Lecturer::create([
        'user_id' => $adminUser->id,
        'faculty_id' => $faculty->id,
        'nip' => '19880011',
        'unit_kerja' => 'Fakultas Kedokteran',
        'is_univ_judge' => false,
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('superadmin.delegation.juries.toggle', $lecturer));

    $response->assertNotFound();

    $this->assertDatabaseHas('lecturers', [
        'id' => $lecturer->id,
        'is_univ_judge' => false,
    ]);
});

test('super admin can filter committees by faculty or university level', function () {
    $superAdmin = createVerifiedSuperAdmin();

    $faculty = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $adminFaculty = User::create([
        'name' => 'Akun Fakultas Uji 101',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_fakultas',
        'faculty_id' => $faculty->id,
        'email_verified_at' => now(),
    ]);

    $adminUniversity = User::create([
        'name' => 'Akun Universitas Uji 202',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'admin_univ',
        'faculty_id' => null,
        'email_verified_at' => now(),
    ]);

    $universityResponse = $this->actingAs($superAdmin)
        ->get(route('superadmin.committees.index', ['stage' => 'universitas']));

    $universityResponse->assertOk();
    $universityResponse->assertSeeText($adminUniversity->name);
    $universityResponse->assertDontSeeText($adminFaculty->name);

    $facultyResponse = $this->actingAs($superAdmin)
        ->get(route('superadmin.committees.index', ['stage' => 'fakultas', 'faculty_id' => $faculty->id]));

    $facultyResponse->assertOk();
    $facultyResponse->assertSeeText($adminFaculty->name);
    $facultyResponse->assertDontSeeText($adminUniversity->name);
});
