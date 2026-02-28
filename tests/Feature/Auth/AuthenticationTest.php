<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    // 1. Buat user dengan password dan role yang PASTI
    $user = User::factory()->create([
        'role' => 'mahasiswa',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // 2. Lakukan proses login
    $response = $this->post('/login', [
        // the login form uses a single "login" field (email, name, or NIM)
        'login' => $user->email,
        'password' => 'password',
    ]);

    // 3. Pastikan berhasil login dan diarahkan dengan benar
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
