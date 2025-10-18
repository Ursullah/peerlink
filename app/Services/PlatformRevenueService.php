<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\PlatformRevenue;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformRevenueService
{
    // Platform revenue percentages
    const INTEREST_COMMISSION_RATE = 0.15; // 15% of interest goes to platform

    const TRANSACTION_FEE_RATE = 0.02; // 2% transaction fee

    const LATE_FEE_RATE = 0.05; // 5% late fee

    const PROCESSING_FEE_FIXED = 50; // KES 0.50 fixed processing fee

    /**
     * Calculate and record interest commission
     */
    public function recordInterestCommission(Loan $loan)
    {
        try {
            $interestAmount = $loan->interest_amount;
            $commissionAmount = (int) ($interestAmount * self::INTEREST_COMMISSION_RATE);

            if ($commissionAmount > 0) {
                PlatformRevenue::create([
                    'type' => 'interest_commission',
                    'source_id' => $loan->id,
                    'source_type' => Loan::class,
                    'amount' => $commissionAmount,
                    'percentage' => self::INTEREST_COMMISSION_RATE * 100,
                    'description' => "Interest commission from Loan #{$loan->id}",
                ]);

                Log::info("Recorded interest commission for Loan #{$loan->id}: KES ".number_format($commissionAmount / 100, 2));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to record interest commission for Loan #{$loan->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate and record transaction fee
     */
    public function recordTransactionFee(Transaction $transaction)
    {
        try {
            $transactionAmount = abs($transaction->amount);
            $feeAmount = (int) ($transactionAmount * self::TRANSACTION_FEE_RATE);

            // Add fixed processing fee
            $totalFee = $feeAmount + self::PROCESSING_FEE_FIXED;

            if ($totalFee > 0) {
                PlatformRevenue::create([
                    'type' => 'transaction_fee',
                    'source_id' => $transaction->id,
                    'source_type' => Transaction::class,
                    'amount' => $totalFee,
                    'percentage' => self::TRANSACTION_FEE_RATE * 100,
                    'description' => "Transaction fee for {$transaction->type} transaction #{$transaction->id}",
                ]);

                Log::info("Recorded transaction fee for Transaction #{$transaction->id}: KES ".number_format($totalFee / 100, 2));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to record transaction fee for Transaction #{$transaction->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record late fee for overdue loans
     */
    public function recordLateFee(Loan $loan, int $daysOverdue)
    {
        try {
            $loanAmount = $loan->principal_amount;
            $lateFeeAmount = (int) ($loanAmount * self::LATE_FEE_RATE);

            if ($lateFeeAmount > 0) {
                PlatformRevenue::create([
                    'type' => 'late_fee',
                    'source_id' => $loan->id,
                    'source_type' => Loan::class,
                    'amount' => $lateFeeAmount,
                    'percentage' => self::LATE_FEE_RATE * 100,
                    'description' => "Late fee for Loan #{$loan->id} ({$daysOverdue} days overdue)",
                ]);

                Log::info("Recorded late fee for Loan #{$loan->id}: KES ".number_format($lateFeeAmount / 100, 2));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to record late fee for Loan #{$loan->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get total revenue for a period
     */
    public function getTotalRevenue($startDate = null, $endDate = null)
    {
        $query = PlatformRevenue::query();

        if ($startDate && $endDate) {
            $query->forPeriod($startDate, $endDate);
        }

        return $query->totalAmount();
    }

    /**
     * Get revenue breakdown by type
     */
    public function getRevenueBreakdown($startDate = null, $endDate = null)
    {
        $query = PlatformRevenue::query();

        if ($startDate && $endDate) {
            $query->forPeriod($startDate, $endDate);
        }

        return $query->select('type', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => $item->total_amount];
            });
    }

    /**
     * Get monthly revenue trend
     */
    public function getMonthlyRevenueTrend($months = 12)
    {
        return PlatformRevenue::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get platform revenue statistics
     */
    public function getRevenueStats()
    {
        $totalRevenue = $this->getTotalRevenue();
        $breakdown = $this->getRevenueBreakdown();
        $monthlyTrend = $this->getMonthlyRevenueTrend(6);

        return [
            'total_revenue' => $totalRevenue,
            'formatted_total' => 'KES '.number_format($totalRevenue / 100, 2),
            'breakdown' => $breakdown,
            'monthly_trend' => $monthlyTrend,
            'top_revenue_source' => $breakdown->sortDesc()->keys()->first(),
            'average_monthly' => $monthlyTrend->avg('total_amount'),
        ];
    }
}
