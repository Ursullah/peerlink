<?php

namespace Tests\Feature;

use App\Jobs\InitiatePayHeroPayment; // Import the Job
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue; // Import Queue facade
use Tests\TestCase;

class BorrowerRepaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function borrower_can_initiate_loan_repayment_which_creates_pending_transaction_and_dispatches_job(): void
    {
        Queue::fake(); // Prevent jobs from actually running

        // 1. Arrange: Create users, request, and an ACTIVE loan
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);
        $borrower->wallet()->create(); // Needs wallet for transaction

        $loanRequest = LoanRequest::create([
            'user_id' => $borrower->id,
            'amount' => 10000, 'repayment_period' => 15, 'interest_rate' => 10,
            'reason' => 'Repay Test', 'collateral_locked' => 2000, 'status' => 'funded',
        ]);

        $loan = Loan::create([
            'loan_request_id' => $loanRequest->id,
            'borrower_id' => $borrower->id,
            'lender_id' => $lender->id,
            'principal_amount' => 10000,
            'interest_amount' => 1000,
            'total_repayable' => 11000,
            'status' => 'active', // MUST be active to repay
            'due_date' => now()->addDays(15),
        ]);

        // 2. Act: Simulate borrower clicking repay
        $response = $this->actingAs($borrower)
                         ->post(route('loans.repay', $loan));

        // 3. Assert: Check results
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check pending transaction created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'transactionable_id' => $loan->id,
            'transactionable_type' => Loan::class,
            'type' => 'repayment',
            'amount' => -$loan->total_repayable,
            'status' => 'pending',
            // Check payhero_transaction_id starts with REPAY_ (optional but good)
            'payhero_transaction_id' => expect(fn ($value) => str_starts_with($value, 'REPAY_')),
        ]);

        // Check that the job was dispatched with the correct transaction
        Queue::assertPushed(InitiatePayHeroPayment::class, function ($job) use ($loan) {
            // Ensure the job has the transaction we expect (can check ID or other properties)
            return $job->transaction->transactionable_id === $loan->id &&
                   $job->transaction->type === 'repayment';
        });

        // Ensure loan status is still active (webhook handles the update)
        $this->assertEquals('active', $loan->fresh()->status);
    }
}