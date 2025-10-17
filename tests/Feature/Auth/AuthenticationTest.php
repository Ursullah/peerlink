<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    // Use phone_number instead of email for login
    $response = $this->post('/login', [
        'phone_number' => $user->phone_number,
        'password' => 'password', // Assumes default factory password
    ]);

    $this->assertAuthenticated();
    // Check if redirecting to the correct dashboard based on role (optional enhancement)
    // For now, the default redirect check is fine if role redirects are handled elsewhere.
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'phone_number' => $user->phone_number,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest(); // Asserts the user is NOT authenticated
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
