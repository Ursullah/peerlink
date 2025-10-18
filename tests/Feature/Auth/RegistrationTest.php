<?php

use App\Models\User;

test('new users can register', function () {
    // 1. Arrange: Prepare the complete user data
    $userData = [
        'name' => 'Test User',
        'phone_number' => '0712345678',
        'email' => 'test@example.com',
        'national_id' => '12345678', // Add National ID
        'role' => 'borrower',        // Add Role
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // 2. Act: Simulate the user submitting the registration form
    $response = $this->post('/register', $userData);

    // 3. Assert: Check the results
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();

    // Check if the user was created in the database
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'phone_number' => '0712345678',
        'email' => 'test@example.com',
        'national_id' => '12345678',
        'role' => 'borrower',
    ]);

    // Check if the wallet was created
    $user = User::where('email', 'test@example.com')->first();
    $this->assertNotNull($user->wallet);
    $this->assertEquals(0, $user->wallet->balance);
});
