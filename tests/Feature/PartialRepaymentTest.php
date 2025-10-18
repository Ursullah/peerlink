<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartialRepaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_borrower_can_make_partial_repayment()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $borrower->id,
            'amount' => 100000, // KES 1,000
            'status' => 'funded',
        ]);

        $loan = Loan::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        // Give borrower enough wallet balance
        $borrower->wallet->update(['balance' => 50000]); // KES 500

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 500, // KES 500 partial payment
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check loan was updated
        $loan->refresh();
        $this->assertEquals(50000, $loan->amount_repaid);
        $this->assertEquals('active', $loan->status); // Still active, not fully repaid

        // Check transactions were created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'partial_repayment',
            'amount' => -50000,
            'status' => 'successful',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $lender->id,
            'type' => 'loan_repayment_credit',
            'amount' => 50000,
            'status' => 'successful',
        ]);
    }

    public function test_partial_repayment_calculates_proportional_interest()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000, // KES 1,000
            'interest_amount' => 12500, // KES 125
            'total_repayable' => 112500, // KES 1,125
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        $borrower->wallet->update(['balance' => 56250]); // KES 562.50 (50% of total)

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 562.50, // 50% of total repayable
            ]);

        $loan->refresh();

        // Should have repaid 50% of the loan
        $this->assertEquals(56250, $loan->amount_repaid);
        $this->assertEquals('active', $loan->status);
    }

    public function test_full_repayment_after_partial_payments_releases_collateral()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $borrower->id,
            'amount' => 100000,
            'collateral_locked' => 20000, // KES 200
            'status' => 'funded',
        ]);

        $loan = Loan::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 56250, // Already 50% repaid
        ]);

        $borrower->wallet->update(['balance' => 56250]); // Remaining amount

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 562.50, // Complete the repayment
            ]);

        $loan->refresh();
        $loanRequest->refresh();
        $borrower->wallet->refresh();

        // Loan should be fully repaid
        $this->assertEquals(112500, $loan->amount_repaid);
        $this->assertEquals('repaid', $loan->status);
        $this->assertEquals('repaid', $loanRequest->status);

        // Collateral should be released
        $this->assertEquals(20000, $borrower->wallet->balance);
    }

    public function test_partial_repayment_increases_reputation_proportionally()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create([
            'role' => 'borrower',
            'reputation_score' => 50,
        ]);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        $borrower->wallet->update(['balance' => 56250]); // 50% of total

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 562.50, // 50% repayment
            ]);

        $borrower->refresh();

        // Should get proportional reputation increase (max 5 points for partial)
        $this->assertEquals(52, $borrower->reputation_score); // 50 + 2 points (50% of 5)
    }

    public function test_smart_repayment_uses_wallet_and_stk_for_insufficient_funds()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        // Give borrower partial wallet balance
        $borrower->wallet->update(['balance' => 30000]); // KES 300 (not enough for full repayment)

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000, // KES 1,000 (more than wallet balance)
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Should contain message about using wallet funds and STK
        $this->assertStringContainsString('Insufficient funds! Used KES', session('success'));
        $this->assertStringContainsString('Please enter your M-Pesa PIN', session('success'));
    }

    public function test_partial_repayment_validation_works_correctly()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        // Test minimum amount validation
        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 5, // Below minimum of KES 10
            ]);

        $response->assertSessionHasErrors(['amount']);

        // Test maximum amount validation
        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 2000, // Above total repayable
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_only_borrower_can_make_partial_repayment()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);
        $otherUser = User::factory()->create(['role' => 'borrower']);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        $response = $this->actingAs($otherUser)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 500,
            ]);

        $response->assertStatus(403);
    }

    public function test_partial_repayment_creates_correct_transaction_records()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0,
        ]);

        $borrower->wallet->update(['balance' => 50000]);

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 500,
            ]);

        // Check borrower transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'transactionable_id' => $loan->id,
            'transactionable_type' => Loan::class,
            'type' => 'partial_repayment',
            'amount' => -50000,
            'status' => 'successful',
        ]);

        // Check lender transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $lender->id,
            'type' => 'loan_repayment_credit',
            'amount' => 50000,
            'status' => 'successful',
        ]);
    }
}
