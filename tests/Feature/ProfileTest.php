<?php

use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('student cannot move to another faculty through academic profile update', function () {
    $facultyTeknik = Faculty::create([
        'name' => 'Fakultas Teknik',
        'slug' => 'fakultas-teknik',
    ]);

    $facultyEkonomi = Faculty::create([
        'name' => 'Fakultas Ekonomi',
        'slug' => 'fakultas-ekonomi',
    ]);

    $user = User::factory()->create([
        'role' => 'mahasiswa',
        'faculty_id' => $facultyTeknik->id,
    ]);

    Student::create([
        'user_id' => $user->id,
        'faculty_id' => $facultyTeknik->id,
        'nim' => 'A11223344',
        'prodi' => 'Informatika',
        'semester' => 6,
        'ipk' => 3.75,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.academic.update'), [
            'faculty_id' => $facultyEkonomi->id,
            'nim' => 'A11223344',
            'prodi' => 'Informatika',
            'semester' => 7,
            'ipk' => 3.8,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();

    expect($user->student->faculty_id)->toBe($facultyTeknik->id)
        ->and($user->faculty_id)->toBe($facultyTeknik->id);
});
