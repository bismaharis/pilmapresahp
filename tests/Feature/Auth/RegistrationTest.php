<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $faculty = \App\Models\Faculty::create(['name' => 'Fakultas Dummy', 'slug' => 'dummy']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'mahasiswa',
        'faculty_id' => $faculty->id,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    // sanity-check database state so the test is more meaningful
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'faculty_id' => $faculty->id,
        'role' => 'mahasiswa',
    ]);
    $this->assertDatabaseHas('pilmapres_periods', [
        'is_active' => true,
    ]);
    $this->assertDatabaseCount('registrations', 1);
});
