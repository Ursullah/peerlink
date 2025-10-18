<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\PlatformRevenue;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_ complete_loan_lifecycle_with_revenue_tracking()
    {
        // Create users
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);
        
        // Give lender funds
        $lender->wallet->update(['balance' => 200000]); // KES 2,000
        
        // Give borrower collateral
        $borrower->wallet->update(['balance' => 20000]); // KES 200 for collateral

        // 1. Create loan request
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $borrower->id,
            'amount' => 100000, // KES 1,000
            'interest_rate' => 12.5,
            'status' => 'pending_approval'
        ]);

        // 2. Admin approves loan request
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('admin.loans.approve', $loanRequest));

        $loanRequest->refresh();
        $this->assertEquals('approved', $loanRequest->status);

        // 3. Lender funds the loan
        $this->actingAs($lender)
            ->post(route('lender.loans.fund', $loanRequest), [
                'amount' => 100000 // KES 1,000
            ]);

        $loanRequest->refresh();
        $this->assertEquals('funded', $loanRequest->status);

        // Check loan was created
        $loan = Loan::where('loan_request_id', $loanRequest->id)->first();
        $this->assertNotNull($loan);
        $this->assertEquals('active', $loan->status);

        // Check platform revenue was recorded
        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'interest_commission',
            'source_id' => $loan->id,
            'source_type' => Loan::class
        ]);

        // 4. Borrower makes partial repayment
        $borrower->wallet->update(['balance' => 50000]); // KES 500

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 500 // KES 500
            ]);

        $loan->refresh();
        $this->assertEquals(50000, $loan->amount_repaid);
        $this->assertEquals('active', $loan->status);

        // 5. Borrower completes repayment
        $borrower->wallet->update(['balance' => 62500]); // Remaining amount

        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 625 // Complete repayment
            ]);

        $loan->refresh();
        $loanRequest->refresh();
        $this->assertEquals(112500, $loan->amount_repaid);
        $this->assertEquals('repaid', $loan->status);
        $this->assertEquals('repaid', $loanRequest->status);

        // Check collateral was released
        $borrower->wallet->refresh();
        $this->assertEquals(20000, $borrower->wallet->balance); // Collateral returned

        // Check reputation was increased
        $borrower->refresh();
        $this->assertGreaterThan(0, $borrower->reputation_score);
    }

    public function test_ multi_lender_funding_with_revenue_tracking()
    {
        // Create multiple lenders
        $lender1 = User::factory()->create(['role' => 'lender']);
        $lender2 = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);
        
        // Give lenders funds
        $lender1->wallet->update(['balance' => 100000]); // KES 1,000
        $lender2->wallet->update(['balance' => 100000]); // KES 1,000
        
        // Give borrower collateral
        $borrower->wallet->update(['balance' => 40000]); // KES 400 for collateral

        // Create and approve loan request
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $borrower->id,
            'amount' => 200000, // KES 2,000
            'interest_rate' => 12.5,
            'status' => 'approved'
        ]);

        // Lender 1 funds 50%
        $this->actingAs($lender1)
            ->post(route('lender.loans.fund', $loanRequest), [
                'amount' => 100000 // KES 1,000
            ]);

        // Lender 2 funds remaining 50%
        $this->actingAs($lender2)
            ->post(route('lender.loans.fund', $loanRequest), [
                'amount' => 100000 // KES 1,000
            ]);

        $loanRequest->refresh();
        $this->assertEquals('funded', $loanRequest->status);

        // Check both loans were created
        $loans = Loan::where('loan_request_id', $loanRequest->id)->get();
        $this->assertCount(2, $loans);

        // Check platform revenue was recorded for both loans
        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'interest_commission',
            'source_id' => $loans[0]->id
        ]);
        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'interest_commission',
            'source_id' => $loans[1]->id
        ]);
    }

    public function test_ smart_repayment_with_insufficient_funds()
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
        $borrower->wallet->update(['balance' => 30000]); // KES 300

        $response = $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1000 // KES 1,000 (more than wallet)
            ]);

        $response->assertSessionHas('success');
        
        // Should use wallet funds and create STK transaction
        $borrower->wallet->refresh();
        $this->assertEquals(0, $borrower->wallet->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $borrower->id,
            'type' => 'stk_repayment',
            'amount' => -70000, // Shortfall amount
            'status' => 'pending'
        ]);
    }

    public function test_ platform_revenue_calculation_accuracy()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);
        
        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000, // KES 1,000
            'interest_amount' => 12500, // KES 125
            'total_repayable' => 112500,
            'status' => 'active'
        ]);

        // Record interest commission
        $revenueService = new \App\Services\PlatformRevenueService();
        $revenueService->recordInterestCommission($loan);

        // Check commission calculation (15% of interest)
        $expectedCommission = (int) (12500 * 0.15); // 1,875 cents
        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'interest_commission',
            'amount' => $expectedCommission,
            'percentage' => 15.00
        ]);
    }

    public function test_ reputation_system_capping()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create([
            'role' => 'borrower',
            'reputation_score' => 95 // Near maximum
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

        $borrower->wallet->update(['balance' => 112500]);

        // Full repayment should only increase reputation to 100
        $this->actingAs($borrower)
            ->post(route('loans.partial-repay', $loan), [
                'amount' => 1125 // Full repayment
            ]);

        $borrower->refresh();
        $this->assertEquals(100, $borrower->reputation_score); // Capped at 100
    }

    public function test_ transaction_fee_tracking()
    {
        $user = User::factory()->create();
        
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 100000, // KES 1,000
            'status' => 'successful'
        ]);

        // Record transaction fee
        $revenueService = new \App\Services\PlatformRevenueService();
        $revenueService->recordTransactionFee($transaction);

        // Check fee calculation (2% + fixed fee)
        $expectedFee = (int) (100000 * 0.02) + 50; // 2,050 cents
        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'transaction_fee',
            'amount' => $expectedFee,
            'percentage' => 2.00
        ]);
    }

    public function test_ late_fee_calculation()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);
        
        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000, // KES 1,000
            'status' => 'active'
        ]);

        // Record late fee
        $revenueService = new \App\Services\PlatformRevenueService();
        $revenueService->recordLateFee($loan, 5); // 5 days overdue

        // Check late fee calculation (5% of principal)
        $expectedLateFee = (int) (100000 * 0.05); // 5,000 cents
        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'late_fee',
            'amount' => $expectedLateFee,
            'percentage' => 5.00
        ]);
    }

    public function test_ end_to_end_user_journey()
    {
        // 1. User registration
        $user = User::factory()->create(['role' => 'borrower']);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        // 2. Wallet setup
        $user->wallet->update(['balance' => 50000]); // KES 500
        $this->assertEquals(50000, $user->wallet->balance);

        // 3. Create loan request
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 100000,
            'status' => 'pending_approval'
        ]);

        // 4. Admin approval
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('admin.loans.approve', $loanRequest));

        // 5. Lender funding
        $lender = User::factory()->create(['role' => 'lender']);
        $lender->wallet->update(['balance' => 100000]);
        
        $this->actingAs($lender)
            ->post(route('lender.loans.fund', $loanRequest), [
                'amount' => 100000
            ]);

        // 6. Repayment
        $user->wallet->update(['balance' => 112500]); // Full repayment amount
        
        $this->actingAs($user)
            ->post(route('loans.partial-repay', Loan::first()), [
                'amount' => 1125 // Full repayment
            ]);

        // Verify complete journey
        $loan = Loan::first();
        $this->assertEquals('repaid', $loan->status);
        $this->assertEquals(112500, $loan->amount_repaid);
        
        $user->refresh();
        $this->assertGreaterThan(0, $user->reputation_score);
    }
}
