<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PayHeroService;
use Throwable;

class PayHeroWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $payhero = app(PayHeroService::class);

        if (!$payhero->verifyWebhook($request)) {
            Log::warning('Invalid PayHero webhook signature received.', ['payload' => $payload]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        Log::info('PayHero Webhook Received (verified):', $payload);

        // 1. Find our local transaction
        // Payloads can have 'id' (PayHero's ID) and 'external_reference' (Our ID)
        $payheroId = $payload['id'] ?? null;
        $externalRef = $payload['external_reference'] ?? null;

        $transaction = Transaction::where('status', 'pending')
            ->where(function ($query) use ($payheroId, $externalRef) {
                if ($payheroId) {
                    $query->orWhere('payhero_transaction_id', $payheroId);
                }
                if ($externalRef) {
                    $query->orWhere('payhero_transaction_id', $externalRef);
                }
            })->first();

        if (!$transaction) {
            Log::warning('Webhook for unknown or already processed transaction received.', $payload);
            return response()->json(['error' => 'Transaction not found or already processed'], 404);
        }
        
        // 2. Handle payment status
        if (isset($payload['status']) && $payload['status'] === true) {
            try {
                DB::transaction(function () use ($transaction) {
                    // Check the type of transaction and call the correct handler
                    if ($transaction->type === 'deposit') {
                        $this->handleDeposit($transaction);
                    } elseif ($transaction->type === 'repayment') {
                        $this->handleRepayment($transaction);
                    }
                });
            } catch (Throwable $e) {
                Log::error('Webhook processing failed:', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Internal server error'], 500);
            }
        } 
         else {
        // If payment failed ( 'FAILED', 'CANCELLED', 'INSUFFICIENT_FUNDS')
        $failureReason = $payload['error_message'] ?? // Specific error from PayHero
                         $payload['message'] ??       // Generic message from PayHero
                         'Payment was not successful.'; // Default fallback

        $transaction->update([
            'status' => 'failed',
            'failure_reason' => $failureReason
        ]);

        // Store a user-friendly message in the session for the next request
        session()->flash('transaction_failed', "Your {$transaction->type} failed: " . $failureReason);

        Log::warning('Webhook received non-successful status.', $payload);
    }

        // 3. Respond to PayHero
        return response()->json(['message' => 'Webhook processed successfully']);
    }

    private function handleDeposit(Transaction $transaction)
    {
        Log::info("Handling successful deposit for transaction {$transaction->id}");
        $transaction->update(['status' => 'successful']);
        $user = $transaction->user;
        $user->wallet->balance += $transaction->amount; // amount is positive
        $user->wallet->save();
    }

    private function handleRepayment(Transaction $transaction)
    {
        Log::info("Handling successful repayment for transaction {$transaction->id}");
        $transaction->update(['status' => 'successful']);
        
        $loan = $transaction->transactionable;
        if (!$loan || !$loan instanceof Loan) {
            Log::error("Could not find associated loan for repayment transaction {$transaction->id}");
            return;
        }

        $borrower = $loan->borrower;
        $lender = $loan->lender;
        $loanRequest = $loan->loanRequest;

        // 1. Credit the Lender's wallet
        $lender->wallet->balance += $loan->total_repayable;
        $lender->wallet->save();
        
        // 2. Release the Borrower's collateral
        $borrower->wallet->balance += $loanRequest->collateral_locked;
        $borrower->wallet->save();

        // 3. Update loan statuses
        $loan->update(['status' => 'repaid', 'amount_repaid' => $loan->total_repayable]);
        $loanRequest->update(['status' => 'repaid']);

        // 4. Update borrower reputation
        $borrower->reputation_score += 10;
        $borrower->save();

        // 5. Log sub-transactions
        $lender->transactions()->create(['type' => 'deposit', 'amount' => $loan->total_repayable, 'status' => 'successful']);
        $borrower->transactions()->create(['type' => 'collateral_release', 'amount' => $loanRequest->collateral_locked, 'status' => 'successful']);
    }
}