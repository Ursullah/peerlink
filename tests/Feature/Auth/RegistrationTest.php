<?php

use App\Models\User;

test('new users can register', function () {
    // 1. Arrange: Prepare the data
    $userData = [
        'name' => 'Test User',
        'phone_number' => '0712345678', // Use a unique phone number
        'email' => 'test@example.com', // Use a unique email
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // 2. Act: Simulate the user submitting the registration form
    $response = $this->post('/register', $userData);

    // 3. Assert: Check the results
    $response->assertRedirect('/dashboard'); // Check if redirected correctly
    $this->assertAuthenticated(); // Check if the user is logged in

    // Check if the user was actually created in the database
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'phone_number' => '0712345678',
        'email' => 'test@example.com',
    ]);

    // Check if the wallet was created
    $user = User::where('email', 'test@example.com')->first();
    $this->assertNotNull($user->wallet);
    $this->assertEquals(0, $user->wallet->balance);
});