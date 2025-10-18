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
        // This is the most important line for debugging.
        Log::info('PayHero STK Push webhook received', $request->all());

        $stkCallback = $request->input('Body.stkCallback');

        if (! $stkCallback) {
            Log::warning('PayHero webhook received with invalid format. Missing Body.stkCallback.');
            return response()->json(['error' => 'Invalid callback format'], 400);
        }

        $merchantRequestID = $stkCallback['MerchantRequestID'];
        $resultCode = $stkCallback['ResultCode'];
        $resultDesc = $stkCallback['ResultDesc'];

        // Find the transaction using the MerchantRequestID you saved when you started the transaction.
        // **IMPORTANT**: Ensure you are saving the MerchantRequestID to this column.
        $transaction = Transaction::where('merchant_request_id', $merchantRequestID)->first();

        if (! $transaction) {
            Log::warning('Transaction not found for MerchantRequestID: '.$merchantRequestID);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Check if transaction was successful (ResultCode = 0)
        if ($resultCode == 0) {
            // It was successful. Get the M-Pesa receipt number.
            $callbackMetadata = $stkCallback['CallbackMetadata']['Item'];
            $mpesaReceiptNumber = '';
            foreach ($callbackMetadata as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $mpesaReceiptNumber = $item['Value'];
                    break;
                }
            }
            $this->processSuccessfulPayment($transaction, $mpesaReceiptNumber);
        } else {
            // The payment failed or was cancelled.
            $this->processFailedPayment($transaction, $resultDesc);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Handle PayHero webhook for payout confirmations
     * NOTE: This assumes payout webhooks have a different, simpler structure.
     * If they also use the M-Pesa format, this will need to be adjusted.
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
        // Prevent processing the same successful transaction twice
        if ($transaction->status === 'successful') {
            Log::warning("Transaction {$transaction->id} already processed as successful.");
            return;
        }

        try {
            DB::transaction(function () use ($transaction, $transactionId) {
                $transaction->update([
                    'status' => 'successful',
                    // This now stores the actual M-Pesa Receipt Number
                    'payhero_transaction_id' => $transactionId,
                ]);

                // Handle different transaction types
                if ($transaction->type === 'deposit') {
                    $this->processDepositSuccess($transaction);
                } elseif ($transaction->type === 'repayment' || $transaction->type === 'stk_repayment') {
                    $this->processRepaymentSuccess($transaction);
                }

                // Record platform revenue for successful transactions
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

        Log::info("Payment failed for transaction {$transaction->id} with reason: {$status}");
    }

    // --- All your other functions (processSuccessfulPayout, processDepositSuccess, etc.) are perfect and do not need to be changed. ---
    // --- They are omitted here for brevity but should remain in your file. ---

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
            });
            Log::info("Successfully processed payout for transaction {$transaction->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to process successful payout for transaction {$transaction->id}", ['error' => $e->getMessage()]);
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
        $this->executeMultiLenderRepayment($loanRequest, abs($transaction->amount));
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

                if ($loan->status === 'repaid') {
                    $borrower->wallet->increment('balance', $loanRequest->collateral_locked);
                    $loanRequest->update(['status' => 'repaid']);
                    $newReputation = min(100, $borrower->reputation_score + 10);
                    $borrower->update(['reputation_score' => $newReputation]);
                }
            });
            Log::info("Loan repayment successful for Loan {$loan->id}");
        } catch (\Throwable $e) {
            Log::error("Failed to process loan repayment for Loan {$loan->id}", ['error' => $e->getMessage()]);
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
        // ... (your existing logic)
    }
}
