<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartRepaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_ smart_repayment_uses_wallet_funds_when_available()
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
            'amount_repaid' => 0
        ]);

        // Give borrower enough wallet balance
        $borrower->wallet->update(['balance' => 112500]);

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1125 // Full repayment
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $loan->refresh();
        $this->assertEquals(112500, $loan->amount_repaid);
        $this->assertEquals('repaid', $loan->status);
    }

    public function test_ smart_repayment_uses_partial_wallet_and_stk_for_shortfall()
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
            'amount_repaid' => 0
        ]);

        // Give borrower partial wallet balance
        $borrower->wallet->update(['balance' => 50000]); // KES 500

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000 (more than wallet)
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Should use wallet funds first
        $borrower->wallet->refresh();
        $this->assertEquals(0, $borrower->wallet->balance);

        // Should create STK transaction for shortfall
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'stk_repayment',
            'amount' => -50000, // Shortfall amount
            'status' => 'pending'
        ]);
    }

    public function test_ smart_repayment_initiates_full_stk_when_no_wallet_funds()
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
            'amount_repaid' => 0
        ]);

        // No wallet balance
        $borrower->wallet->update(['balance' => 0]);

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Should create full STK transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'repayment',
            'amount' => -100000, // Full amount
            'status' => 'pending'
        ]);
    }

    public function test_ smart_repayment_calculates_proportional_interest_correctly()
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
            'amount_repaid' => 0
        ]);

        // Give borrower partial wallet balance
        $borrower->wallet->update(['balance' => 56250]); // 50% of total

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000
            ]);

        $loan->refresh();
        
        // Should have used wallet funds (50% of total)
        $this->assertEquals(56250, $loan->amount_repaid);
        $this->assertEquals('active', $loan->status);
    }

    public function test_ smart_repayment_updates_reputation_correctly()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create([
            'role' => 'borrower',
            'reputation_score' => 50
        ]);
        
        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0
        ]);

        // Give borrower partial wallet balance
        $borrower->wallet->update(['balance' => 56250]); // 50% of total

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000
            ]);

        $borrower->refresh();
        
        // Should get proportional reputation increase
        $this->assertEquals(52, $borrower->reputation_score); // 50 + 2 points
    }

    public function test_ smart_repayment_handles_database_transactions_correctly()
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
            'amount_repaid' => 0
        ]);

        // Give borrower partial wallet balance
        $borrower->wallet->update(['balance' => 50000]);

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000
            ]);

        // Check wallet transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'partial_repayment',
            'amount' => -50000,
            'status' => 'successful'
        ]);

        // Check lender was credited
        $this->assertDatabaseHas('transactions', [
            'user_id' => $lender->id,
            'type' => 'loan_repayment_credit',
            'amount' => 50000,
            'status' => 'successful'
        ]);
    }

    public function test_ smart_repayment_handles_errors_gracefully()
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
            'amount_repaid' => 0
        ]);

        // Give borrower partial wallet balance
        $borrower->wallet->update(['balance' => 50000]);

        // Mock a database error by deleting the loan
        $loan->delete();

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_ smart_repayment_creates_correct_stk_payload()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create([
            'role' => 'borrower',
            'phone_number' => '0712345678'
        ]);
        
        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500,
            'total_repayable' => 112500,
            'status' => 'active',
            'amount_repaid' => 0
        ]);

        // No wallet balance
        $borrower->wallet->update(['balance' => 0]);

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000
            ]);

        // Check STK transaction was created with correct external reference
        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'repayment',
            'amount' => -100000,
            'status' => 'pending'
        ]);

        $transaction = Transaction::where('user_id', $borrower->id)
            ->where('type', 'repayment')
            ->first();

        $this->assertNotNull($transaction->payhero_transaction_id);
        $this->assertStringStartsWith('REPAY_', $transaction->payhero_transaction_id);
    }
}
