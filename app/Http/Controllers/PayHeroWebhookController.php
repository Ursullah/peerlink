<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Services\PlatformRevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayHeroWebhookController extends Controller
{
    /**
     * Handle PayHero webhook for payment confirmations
     */
    public function handle(Request $request)
    {
        Log::info('PayHero webhook received', $request->all());

        $status = $request->input('status');
        $externalRef = $request->input('external_reference');
        $transactionId = $request->input('transaction_id');

        if (! $externalRef) {
            Log::warning('PayHero webhook missing external_reference');

            return response()->json(['error' => 'Missing external reference'], 400);
        }

        // Find the transaction by external reference
        $transaction = Transaction::where('payhero_transaction_id', $externalRef)->first();

        if (! $transaction) {
            Log::warning('Transaction not found for external reference: '.$externalRef);

            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($status === 'successful') {
            $this->processSuccessfulPayment($transaction, $transactionId);
        } else {
            $this->processFailedPayment($transaction, $status);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Handle PayHero webhook for payout confirmations
     */
    public function handlePayout(Request $request)
    {
        Log::info('PayHero payout webhook received', $request->all());

        $status = $request->input('status');
        $externalRef = $request->input('external_reference');
        $transactionId = $request->input('transaction_id');

        if (! $externalRef) {
            Log::warning('PayHero payout webhook missing external_reference');

            return response()->json(['error' => 'Missing external reference'], 400);
        }

        // Find the transaction by external reference
        $transaction = Transaction::where('payhero_transaction_id', $externalRef)->first();

        if (! $transaction) {
            Log::warning('Payout transaction not found for external reference: '.$externalRef);

            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($status === 'successful') {
            $this->processSuccessfulPayout($transaction, $transactionId);
        } else {
            $this->processFailedPayout($transaction, $status);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment(Transaction $transaction, string $transactionId)
    {
        try {
            DB::transaction(function () use ($transaction, $transactionId) {
                $transaction->update([
                    'status' => 'successful',
                    'payhero_transaction_id' => $transactionId,
                ]);

                // Handle different transaction types
                if ($transaction->type === 'deposit') {
                    $this->processDepositSuccess($transaction);
                } elseif ($transaction->type === 'repayment' || $transaction->type === 'stk_repayment') {
                    $this->processRepaymentSuccess($transaction);
                } elseif ($transaction->type === 'withdrawal') {
                    $this->processWithdrawalSuccess($transaction);
                }

                // Record platform revenue for successful transactions
                $revenueService = new PlatformRevenueService;
                $revenueService->recordTransactionFee($transaction);
            });

            Log::info("Successfully processed payment for transaction {$transaction->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to process successful payment for transaction {$transaction->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process failed payment
     */
    private function processFailedPayment(Transaction $transaction, string $status)
    {
        $transaction->update([
            'status' => 'failed',
            'failure_reason' => $status,
        ]);

        Log::info("Payment failed for transaction {$transaction->id} with status: {$status}");
    }

    /**
     * Process successful payout
     */
    private function processSuccessfulPayout(Transaction $transaction, string $transactionId)
    {
        try {
            DB::transaction(function () use ($transaction, $transactionId) {
                $transaction->update([
                    'status' => 'successful',
                    'payhero_transaction_id' => $transactionId,
                ]);

                if ($transaction->type === 'withdrawal') {
                    $this->processWithdrawalSuccess($transaction);
                }
            });

            Log::info("Successfully processed payout for transaction {$transaction->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to process successful payout for transaction {$transaction->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process failed payout
     */
    private function processFailedPayout(Transaction $transaction, string $status)
    {
        $transaction->update([
            'status' => 'failed',
            'failure_reason' => $status,
        ]);

        Log::info("Payout failed for transaction {$transaction->id} with status: {$status}");
    }

    /**
     * Process successful deposit
     */
    private function processDepositSuccess(Transaction $transaction)
    {
        $user = $transaction->user;
        $user->wallet->increment('balance', abs($transaction->amount));

        Log::info("Deposit successful for user {$user->id}, amount: ".abs($transaction->amount));
    }

    /**
     * Process successful repayment
     */
    private function processRepaymentSuccess(Transaction $transaction)
    {
        if ($transaction->transactionable_type === LoanRequest::class) {
            $this->processLoanRequestRepayment($transaction);
        } elseif ($transaction->transactionable_type === Loan::class) {
            $this->processLoanRepayment($transaction);
        }
    }

    /**
     * Process loan request repayment (multi-lender)
     */
    private function processLoanRequestRepayment(Transaction $transaction)
    {
        $loanRequest = $transaction->transactionable;
        $borrower = $loanRequest->borrower;
        $allLoans = $loanRequest->loans;
        $totalToRepay = abs($transaction->amount);

        // Execute multi-lender repayment logic
        $this->executeMultiLenderRepayment($loanRequest, $totalToRepay);

        Log::info("Loan request repayment successful for LoanRequest {$loanRequest->id}");
    }

    /**
     * Process individual loan repayment
     */
    private function processLoanRepayment(Transaction $transaction)
    {
        $loan = $transaction->transactionable;
        $borrower = $loan->borrower;
        $lender = $loan->lender;
        $loanRequest = $loan->loanRequest;
        $amount = abs($transaction->amount);

        try {
            DB::transaction(function () use ($loan, $borrower, $lender, $loanRequest, $amount) {
                // Credit lender
                $lender->wallet->increment('balance', $amount);

                // Update loan
                $newAmountRepaid = $loan->amount_repaid + $amount;
                $loan->update([
                    'amount_repaid' => $newAmountRepaid,
                    'status' => $newAmountRepaid >= $loan->total_repayable ? 'repaid' : 'active',
                ]);

                // If fully repaid, release collateral and update loan request
                if ($newAmountRepaid >= $loan->total_repayable) {
                    $borrower->wallet->increment('balance', $loanRequest->collateral_locked);
                    $loanRequest->update(['status' => 'repaid']);

                    // Increase borrower's reputation (capped at 100)
                    $newReputation = min(100, $borrower->reputation_score + 10);
                    $borrower->update(['reputation_score' => $newReputation]);
                } else {
                    // Partial reputation increase
                    $repaymentRatio = $amount / $loan->total_repayable;
                    $reputationIncrease = (int) ($repaymentRatio * 5);
                    $newReputation = min(100, $borrower->reputation_score + $reputationIncrease);
                    $borrower->update(['reputation_score' => $newReputation]);
                }

                // Log lender transaction
                $lender->transactions()->create([
                    'type' => 'loan_repayment_credit',
                    'amount' => $amount,
                    'status' => 'successful',
                ]);

                // If fully repaid, log collateral release
                if ($newAmountRepaid >= $loan->total_repayable) {
                    $borrower->transactions()->create([
                        'type' => 'collateral_release',
                        'amount' => $loanRequest->collateral_locked,
                        'status' => 'successful',
                    ]);
                }
            });

            Log::info("Loan repayment successful for Loan {$loan->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to process loan repayment for Loan {$loan->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process successful withdrawal
     */
    private function processWithdrawalSuccess(Transaction $transaction)
    {
        // Withdrawal is already processed when initiated
        // This just confirms the payout was successful
        Log::info("Withdrawal successful for transaction {$transaction->id}");
    }

    /**
     * Execute multi-lender repayment logic
     */
    private function executeMultiLenderRepayment(LoanRequest $loanRequest, int $totalToRepay)
    {
        $borrower = $loanRequest->borrower;
        $borrowerWallet = $borrower->wallet;
        $allLoans = $loanRequest->loans;

        // Loop through each partial loan and pay back the respective lender
        foreach ($allLoans as $loan) {
            $lender = $loan->lender;
            $lender->wallet->increment('balance', $loan->total_repayable);
            $loan->update(['status' => 'repaid', 'amount_repaid' => $loan->total_repayable]);
            Transaction::create([
                'user_id' => $lender->id,
                'type' => 'loan_repayment_credit',
                'amount' => $loan->total_repayable,
                'status' => 'successful',
            ]);
        }

        // Update main LoanRequest status
        $loanRequest->update(['status' => 'repaid']);

        // Release Borrower's collateral
        $borrowerWallet->increment('balance', $loanRequest->collateral_locked);

        // Increase borrower's reputation (capped at 100)
        $newReputation = min(100, $borrower->reputation_score + 10);
        $borrower->update(['reputation_score' => $newReputation]);

        // Log transactions
        Transaction::create([
            'user_id' => $borrower->id,
            'transactionable_id' => $loanRequest->id,
            'transactionable_type' => LoanRequest::class,
            'type' => 'repayment',
            'amount' => -$totalToRepay,
            'status' => 'successful',
        ]);

        Transaction::create([
            'user_id' => $borrower->id,
            'type' => 'collateral_release',
            'amount' => $loanRequest->collateral_locked,
            'status' => 'successful',
        ]);
    }
}
