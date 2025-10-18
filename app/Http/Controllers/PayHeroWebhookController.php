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
     * Handle PayHero webhook for M-Pesa STK Push payment confirmations
     */
    public function handle(Request $request)
    {
        Log::info('PayHero STK Push webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_content' => $request->getContent(),
        ]);

        // Try to get data from request body first
        $data = $request->all();

        // If no data in body, try to decode raw content
        if (empty($data)) {
            $content = $request->getContent();
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('PayHero webhook received with invalid JSON format.', ['content' => $content]);

                return response()->json(['error' => 'Invalid JSON format'], 400);
            }
        }

        // Handle different PayHero webhook formats
        $externalRef = null;
        $status = null;
        $transactionId = null;

        // Format 1: Direct fields in request
        if (isset($data['external_reference'])) {
            $externalRef = $data['external_reference'];
            $status = $data['status'] ?? $data['Service_status'] ?? null;
            $transactionId = $data['transaction_id'] ?? $data['Transaction_Reference'] ?? null;
        }
        // Format 2: Nested in response field
        elseif (isset($data['response'])) {
            $responseData = is_string($data['response']) ? json_decode($data['response'], true) : $data['response'];
            if ($responseData) {
                $externalRef = $responseData['User_Reference'] ?? $responseData['external_reference'] ?? null;
                $status = $responseData['woocommerce_payment_status'] ?? $responseData['Service_status'] ?? $responseData['status'] ?? null;
                $transactionId = $responseData['Transaction_Reference'] ?? $responseData['transaction_id'] ?? null;
            }
        }
        // Format 3: Direct transaction data
        else {
            $externalRef = $data['User_Reference'] ?? $data['external_reference'] ?? null;
            $status = $data['Service_status'] ?? $data['status'] ?? null;
            $transactionId = $data['Transaction_Reference'] ?? $data['transaction_id'] ?? null;
        }

        if (! $externalRef) {
            Log::warning('PayHero webhook missing external reference.', ['data' => $data]);

            return response()->json(['error' => 'Missing external reference'], 400);
        }

        $transaction = Transaction::where('external_reference', $externalRef)->first();

        if (! $transaction) {
            Log::warning('Transaction not found for external reference: '.$externalRef);

            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Determine if payment was successful
        $isSuccessful = false;
        if ($status) {
            $isSuccessful = in_array(strtolower($status), ['complete', 'successful', 'success', 'completed']);
        }

        if ($isSuccessful) {
            $this->processSuccessfulPayment($transaction, $transactionId);
        } else {
            $this->processFailedPayment($transaction, $status ?? 'Unknown failure reason');
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

        $transaction = Transaction::where('external_reference', $externalRef)->first();

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
    private function processSuccessfulPayment(Transaction $transaction, ?string $transactionId)
    {
        if ($transaction->status === 'successful') {
            Log::warning("Transaction {$transaction->id} already processed as successful.");

            return;
        }

        try {
            DB::transaction(function () use ($transaction, $transactionId) {
                $transaction->update([
                    'status' => 'successful',
                    'payhero_transaction_id' => $transactionId,
                ]);

                if ($transaction->type === 'deposit') {
                    $this->processDepositSuccess($transaction);
                } elseif ($transaction->type === 'repayment' || $transaction->type === 'stk_repayment') {
                    $this->processRepaymentSuccess($transaction);
                }

                $revenueService = new PlatformRevenueService;
                $revenueService->recordTransactionFee($transaction);
            });

            Log::info("Successfully processed payment for transaction {$transaction->id}, M-Pesa ID: {$transactionId}");
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
        $totalToRepay = abs($transaction->amount);
        $this->executeMultiLenderRepayment($loanRequest, $totalToRepay);
        Log::info("Loan request repayment successful for LoanRequest {$loanRequest->id}");
    }

    /**
     * Process individual loan repayment
     */
    private function processLoanRepayment(Transaction $transaction)
    {
        $loan = $transaction->transactionable;
        $amount = abs($transaction->amount);

        try {
            DB::transaction(function () use ($loan, $amount) {
                $borrower = $loan->borrower;
                $lender = $loan->lender;
                $loanRequest = $loan->loanRequest;

                $lender->wallet->increment('balance', $amount);
                $newAmountRepaid = $loan->amount_repaid + $amount;
                $loan->update([
                    'amount_repaid' => $newAmountRepaid,
                    'status' => $newAmountRepaid >= $loan->total_repayable ? 'repaid' : 'active',
                ]);

                if ($newAmountRepaid >= $loan->total_repayable) {
                    $borrower->wallet->increment('balance', $loanRequest->collateral_locked);
                    $loanRequest->update(['status' => 'repaid']);
                    $newReputation = min(100, $borrower->reputation_score + 10);
                    $borrower->update(['reputation_score' => $newReputation]);
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
        Log::info("Withdrawal successful for transaction {$transaction->id}");
    }

    /**
     * Execute multi-lender repayment logic
     */
    private function executeMultiLenderRepayment(LoanRequest $loanRequest, int $totalToRepay)
    {
        $borrower = $loanRequest->borrower;
        $allLoans = $loanRequest->loans;

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

        $loanRequest->update(['status' => 'repaid']);
        $borrower->wallet->increment('balance', $loanRequest->collateral_locked);
        $newReputation = min(100, $borrower->reputation_score + 10);
        $borrower->update(['reputation_score' => $newReputation]);

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
