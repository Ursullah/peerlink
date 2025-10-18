<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase; // Crucial for tests
use Tests\TestCase;

class LoanRequestTest extends TestCase
{
    use RefreshDatabase; // Automatically reset the database for each test

    /** @test */
    public function a_borrower_can_create_a_loan_request_with_sufficient_collateral(): void
    {
        // 1. Arrange: Create a user (borrower) and give them a wallet with balance
        $borrower = User::factory()->create(['role' => 'borrower']); // Create a borrower
        $borrower->wallet()->create(['balance' => 50000]); // Give them KES 500 (in cents)

        // Loan request data
        $loanData = [
            'amount' => 1000,         // Requesting KES 1,000
            'repayment_period' => 30, // 30 days
            'interest_rate' => 15,    // 15%
            'reason' => 'Test loan reason',
        ];

        // Calculate expected collateral (20% of 1000 * 100 cents)
        $expectedCollateral = (1000 * 100) * 0.20;

        // 2. Act: Simulate the logged-in borrower submitting the form
        $response = $this->actingAs($borrower) // Log in as the borrower
            ->post(route('loan-requests.store'), $loanData);

        // 3. Assert: Check the results
        $response->assertRedirect(route('dashboard')); // Check redirect
        $response->assertSessionHas('success');       // Check success message

        // Check if loan request was created in DB
        $this->assertDatabaseHas('loan_requests', [
            'user_id' => $borrower->id,
            'amount' => 1000 * 100, // Amount stored in cents
            'status' => 'pending_approval',
            'collateral_locked' => $expectedCollateral,
        ]);

        // Check if collateral was deducted from wallet
        $this->assertDatabaseHas('wallets', [
            'user_id' => $borrower->id,
            'balance' => 50000 - $expectedCollateral, // Original balance minus collateral
        ]);

        // Check if the collateral lock transaction was recorded
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'collateral_lock',
            'amount' => -$expectedCollateral, // Negative amount
            'status' => 'successful',
        ]);
    }

    /** @test */
    public function a_borrower_cannot_create_a_loan_request_without_sufficient_collateral(): void
    {
        // 1. Arrange: Create a borrower with insufficient funds
        $borrower = User::factory()->create(['role' => 'borrower']);
        $borrower->wallet()->create(['balance' => 1000]); // Only KES 10 (1000 cents)

        $loanData = [
            'amount' => 1000, // Requesting KES 1,000 (needs KES 200 collateral)
            'repayment_period' => 30,
            'interest_rate' => 15,
            'reason' => 'Test loan reason',
        ];

        // 2. Act: Simulate submission
        $response = $this->actingAs($borrower)
            ->post(route('loan-requests.store'), $loanData);

        // 3. Assert: Check results
        $response->assertSessionHasErrors('amount'); // Check for validation error on 'amount' field
        $this->assertDatabaseMissing('loan_requests', [ // Ensure NO request was created
            'user_id' => $borrower->id,
        ]);
        $this->assertEquals(1000, $borrower->wallet->fresh()->balance); // Ensure wallet balance didn't change
    }
}
