<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LenderFundingTest extends TestCase
{
    use RefreshDatabase;

    protected User $lender;
    protected User $borrower;
    protected LoanRequest $loanRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lender = User::factory()->create(['role' => 'lender']);
        $this->lender->wallet()->create(['balance' => 50000]); // KES 500

        $this->borrower = User::factory()->create(['role' => 'borrower']);
        $this->borrower->wallet()->create(['balance' => 0]); // Starts empty

        $this->loanRequest = LoanRequest::create([
            'user_id' => $this->borrower->id,
            'amount' => 25000, // KES 250
            'repayment_period' => 30,
            'interest_rate' => 12.5,
            'reason' => 'Test',
            'collateral_locked' => 5000, // KES 50
            'status' => 'active', // Loan is approved, ready for funding
        ]);
    }

    /** @test */
    public function lender_can_fund_an_active_loan_request_with_sufficient_balance(): void
    {
        $initialLenderBalance = $this->lender->wallet->balance;
        $initialBorrowerBalance = $this->borrower->wallet->balance;
        $loanAmount = $this->loanRequest->amount;

        $response = $this->actingAs($this->lender)
                         ->post(route('lender.loans.fund', $this->loanRequest));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check request status updated
        $this->assertDatabaseHas('loan_requests', [
            'id' => $this->loanRequest->id,
            'status' => 'funded',
        ]);

        // Check wallet balances updated
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->lender->id,
            'balance' => $initialLenderBalance - $loanAmount,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->borrower->id,
            'balance' => $initialBorrowerBalance + $loanAmount,
        ]);

        // Check Loan record created
        $this->assertDatabaseHas('loans', [
            'loan_request_id' => $this->loanRequest->id,
            'lender_id' => $this->lender->id,
            'borrower_id' => $this->borrower->id,
            'principal_amount' => $loanAmount,
            'status' => 'active', // Loan itself is active now
        ]);

        // Check transactions created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->lender->id,
            'type' => 'loan_funding',
            'amount' => -$loanAmount,
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->borrower->id,
            'type' => 'deposit', // Check based on your updated webhook controller logic
            'amount' => $loanAmount,
        ]);
    }

    /** @test */
    public function lender_cannot_fund_an_active_loan_request_without_sufficient_balance(): void
    {
        $this->lender->wallet->update(['balance' => 10000]); // Only KES 100 (loan needs KES 250)

        $response = $this->actingAs($this->lender)
                         ->post(route('lender.loans.fund', $this->loanRequest));

        $response->assertRedirect();
        $response->assertSessionHas('error'); // Check for error message

        // Ensure status and balances didn't change
        $this->assertEquals('active', $this->loanRequest->fresh()->status);
        $this->assertEquals(10000, $this->lender->wallet->fresh()->balance);
        $this->assertEquals(0, $this->borrower->wallet->fresh()->balance);
        $this->assertDatabaseMissing('loans', ['loan_request_id' => $this->loanRequest->id]);
    }
}