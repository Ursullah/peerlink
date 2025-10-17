<?php

namespace Tests\Feature;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $borrower;
    protected LoanRequest $loanRequest;

    // Helper method to set up common data for tests
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->borrower = User::factory()->create(['role' => 'borrower']);
        $this->borrower->wallet()->create(['balance' => 5000]); // KES 50 collateral

        $this->loanRequest = LoanRequest::create([
            'user_id' => $this->borrower->id,
            'amount' => 25000, // KES 250
            'repayment_period' => 30,
            'interest_rate' => 12.5,
            'reason' => 'Test',
            'collateral_locked' => 5000, // 20%
            'status' => 'pending_approval',
        ]);
    }

    /** @test */
    public function admin_can_approve_a_pending_loan_request(): void
    {
        $response = $this->actingAs($this->admin)
                         ->patch(route('admin.loans.approve', $this->loanRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('loan_requests', [
            'id' => $this->loanRequest->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_reject_a_pending_loan_request_and_collateral_is_refunded(): void
    {
        $initialBorrowerBalance = $this->borrower->wallet->balance; // Should be 0 after collateral locked during setUp if we simulated it fully, let's assume it was 5000 before request
        $this->borrower->wallet->update(['balance' => 0]); // Simulate collateral already locked

        $response = $this->actingAs($this->admin)
                         ->patch(route('admin.loans.reject', $this->loanRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check request status
        $this->assertDatabaseHas('loan_requests', [
            'id' => $this->loanRequest->id,
            'status' => 'rejected',
        ]);

        // Check collateral refunded to wallet
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->borrower->id,
            'balance' => $this->loanRequest->collateral_locked, // Balance should now be the refunded collateral
        ]);

        // Check refund transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->borrower->id,
            'type' => 'collateral_release',
            'amount' => $this->loanRequest->collateral_locked, // Positive amount
            'status' => 'successful',
            'transactionable_id' => $this->loanRequest->id,
            'transactionable_type' => LoanRequest::class,
        ]);
    }
}