<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\InitiatePayHeroPayment;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    /**
     * Process the repayment of a specific loan.
     */
    public function repay(Loan $loan)
    {
        // Security Check
        if ($loan->borrower_id !== auth()->id() || $loan->status !== 'active') {
            abort(403, 'Unauthorized or loan is not active for repayment.');
        }

        $borrower = $loan->borrower;
        $borrowerWallet = $borrower->wallet;

        // --- CHECK WALLET BALANCE FIRST ---
        if ($borrowerWallet->balance >= $loan->total_repayable) {
            // User has enough funds for an instant internal repayment
            Log::info("Processing instant wallet repayment for Loan #{$loan->id}");
            
            try {
                DB::transaction(function () use ($loan, $borrower, $borrowerWallet) {
                    $lender = $loan->lender;
                    $loanRequest = $loan->loanRequest;

                    // 1. Debit the Borrower's wallet
                    $borrowerWallet->balance -= $loan->total_repayable;
                    $borrowerWallet->save();

                    // 2. Credit the Lender's wallet
                    $lender->wallet->balance += $loan->total_repayable;
                    $lender->wallet->save();
                    
                    // 3. Release the Borrower's collateral back to their wallet
                    $borrowerWallet->balance += $loanRequest->collateral_locked;
                    $borrowerWallet->save();

                    // 4. Update loan statuses
                    $loan->update(['status' => 'repaid', 'amount_repaid' => $loan->total_repayable]);
                    $loanRequest->update(['status' => 'repaid']);

                    // 5. Increase borrower's reputation
                    $borrower->reputation_score += 10;
                    $borrower->save();

                    // 6. Log all transactions
                    $borrower->transactions()->create(['transactionable_id' => $loan->id, 'transactionable_type' => Loan::class, 'type' => 'repayment', 'amount' => -$loan->total_repayable, 'status' => 'successful']);
                    $lender->transactions()->create(['type' => 'deposit', 'amount' => $loan->total_repayable, 'status' => 'successful']);
                    $borrower->transactions()->create(['type' => 'collateral_release', 'amount' => $loanRequest->collateral_locked, 'status' => 'successful']);
                });

                return back()->with('success', 'Loan successfully repaid from your wallet balance!');

            } catch (\Throwable $e) {
                Log::error("Instant wallet repayment failed for Loan #{$loan->id}", ['error' => $e->getMessage()]);
                return back()->with('error', 'An error occurred during wallet repayment. Please try again.');
            }
        }

        // --- FALLBACK LOGIC: INITIATE STK PUSH ---
        Log::info("Wallet balance insufficient for Loan #{$loan->id}. Initiating STK Push.");
        $amountKES = $loan->total_repayable / 100;
        $phoneNumber = preg_replace('/^0/', '254', $borrower->phone_number);
        $channelId = config('payhero.channel_id');
        $provider = config('payhero.provider', 'm-pesa');
        $externalRef = 'REPAY_' . $loan->id . '_' . Str::random(8);

        // Create the pending transaction
        $transaction = $borrower->transactions()->create([
            'transactionable_id' => $loan->id,
            'transactionable_type' => Loan::class,
            'type' => 'repayment',
            'amount' => -$loan->total_repayable,
            'status' => 'pending',
            'payhero_transaction_id' => $externalRef,
        ]);

        // Dispatch the job
        $payload = [
            'amount' => $amountKES,
            'phone_number' => $phoneNumber,
            'channel_id' => $channelId,
            'provider' => $provider,
            'callback_url' => url('/api/webhooks/payhero'),
            'external_reference' => $externalRef,
        ];

        InitiatePayHeroPayment::dispatch($transaction, $payload);

        return back()->with('success', 'Repayment initiated. Please check your phone and enter your M-Pesa PIN.');
    }
}
