<?php

use App\Models\Faculty;
use App\Models\User;

beforeEach(function () {
    $this->faculty = Faculty::create(['name' => 'Fakultas Teknik', 'slug' => 'teknik']);
    $this->superAdmin = User::factory()->create([
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
});

it('super admin can view participants index', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.participants.index'))
        ->assertStatus(200);
});

it('super admin can store a new participant', function () {
    $response = $this->actingAs($this->superAdmin)
        ->post(route('admin.participants.store'), [
            'name' => 'Test Mahasiswa',
            'email' => 'testmahasiswa@example.com',
            'password' => 'password123',
            'nim' => '1234567890',
            'prodi' => 'Teknik Informatika',
            'faculty_id' => $this->faculty->id,
        ]);

    $response->assertRedirect(route('admin.participants.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'testmahasiswa@example.com',
        'role' => 'mahasiswa',
        'faculty_id' => $this->faculty->id,
    ]);

    $this->assertDatabaseHas('students', [
        'nim' => '1234567890',
        'prodi' => 'Teknik Informatika',
        'faculty_id' => $this->faculty->id,
        'semester' => 1,
    ]);
});
