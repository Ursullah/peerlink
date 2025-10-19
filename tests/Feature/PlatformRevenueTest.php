<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\PlatformRevenue;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PlatformRevenueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected $revenueService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revenueService = new PlatformRevenueService;
    }

    public function test_it_records_interest_commission_when_loan_is_created()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $borrower->id,
            'amount' => 100000, // KES 1,000
            'interest_rate' => 12.5,
        ]);

        $loan = Loan::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000,
            'interest_amount' => 12500, // 12.5% of 100,000
            'total_repayable' => 112500,
        ]);

        $this->revenueService->recordInterestCommission($loan);

        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'interest_commission',
            'source_id' => $loan->id,
            'source_type' => Loan::class,
            'amount' => 1875, // 15% of 12,500
            'percentage' => 15.00,
        ]);
    }

    public function test_it_records_transaction_fee_for_deposits()
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 100000, // KES 1,000
            'status' => 'successful',
        ]);

        $this->revenueService->recordTransactionFee($transaction);

        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'transaction_fee',
            'source_id' => $transaction->id,
            'source_type' => Transaction::class,
            'amount' => 2050, // 2% of 100,000 + 50 fixed fee
            'percentage' => 2.00,
        ]);
    }

    public function test_it_records_late_fee_for_overdue_loans()
    {
        $lender = User::factory()->create(['role' => 'lender']);
        $borrower = User::factory()->create(['role' => 'borrower']);

        $loan = Loan::factory()->create([
            'lender_id' => $lender->id,
            'borrower_id' => $borrower->id,
            'principal_amount' => 100000, // KES 1,000
            'status' => 'active',
        ]);

        $this->revenueService->recordLateFee($loan, 5); // 5 days overdue

        $this->assertDatabaseHas('platform_revenues', [
            'type' => 'late_fee',
            'source_id' => $loan->id,
            'source_type' => Loan::class,
            'amount' => 5000, // 5% of 100,000
            'percentage' => 5.00,
        ]);
    }

    public function test_it_calculates_total_revenue_correctly()
    {
        PlatformRevenue::factory()->create([
            'type' => 'interest_commission',
            'amount' => 100000,
        ]);

        PlatformRevenue::factory()->create([
            'type' => 'transaction_fee',
            'amount' => 50000,
        ]);

        $totalRevenue = $this->revenueService->getTotalRevenue();

        $this->assertEquals(150000, $totalRevenue);
    }

    public function test_it_provides_revenue_breakdown_by_type()
    {
        PlatformRevenue::factory()->create([
            'type' => 'interest_commission',
            'amount' => 100000,
        ]);

        PlatformRevenue::factory()->create([
            'type' => 'interest_commission',
            'amount' => 50000,
        ]);

        PlatformRevenue::factory()->create([
            'type' => 'transaction_fee',
            'amount' => 25000,
        ]);

        $breakdown = $this->revenueService->getRevenueBreakdown();

        $this->assertEquals(150000, $breakdown['interest_commission']);
        $this->assertEquals(25000, $breakdown['transaction_fee']);
    }

    public function test_it_calculates_monthly_revenue_trend()
    {
        // Create revenues for different months
        PlatformRevenue::factory()->create([
            'created_at' => now()->subMonths(2),
            'amount' => 100000,
        ]);

        PlatformRevenue::factory()->create([
            'created_at' => now()->subMonth(),
            'amount' => 150000,
        ]);

        $trend = $this->revenueService->getMonthlyRevenueTrend(12);

        $this->assertCount(2, $trend);
        $this->assertEquals(100000, $trend->first()->total_amount);
    }

    public function test_it_provides_comprehensive_revenue_stats()
    {
        PlatformRevenue::factory()->create([
            'type' => 'interest_commission',
            'amount' => 100000,
        ]);

        PlatformRevenue::factory()->create([
            'type' => 'transaction_fee',
            'amount' => 50000,
        ]);

        $stats = $this->revenueService->getRevenueStats();

        $this->assertArrayHasKey('total_revenue', $stats);
        $this->assertArrayHasKey('formatted_total', $stats);
        $this->assertArrayHasKey('breakdown', $stats);
        $this->assertArrayHasKey('monthly_trend', $stats);
        $this->assertArrayHasKey('top_revenue_source', $stats);
        $this->assertArrayHasKey('average_monthly', $stats);

        $this->assertEquals(150000, $stats['total_revenue']);
        $this->assertEquals('KES 1,500.00', $stats['formatted_total']);
        $this->assertEquals('interest_commission', $stats['top_revenue_source']);
    }
}
