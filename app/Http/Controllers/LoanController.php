<?php

namespace App\Http\Controllers;

use App\Jobs\InitiatePayHeroPayment;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $externalRef = 'REPAY_'.$loan->id.'_'.Str::random(8);

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

    /**
     * Process partial repayment of a specific loan.
     */
    public function partialRepay(Request $request, Loan $loan)
    {
        // Security Check
        if ($loan->borrower_id !== auth()->id() || $loan->status !== 'active') {
            abort(403, 'Unauthorized or loan is not active for repayment.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:'.($loan->total_repayable / 100),
        ]);

        $borrower = $loan->borrower;
        $borrowerWallet = $borrower->wallet;
        $amountInCents = $validated['amount'] * 100;

        // Check if the borrower's wallet has enough funds
        if ($borrowerWallet->balance < $amountInCents) {
            // Smart repayment: Use wallet funds + STK push for the difference
            $walletBalance = $borrowerWallet->balance;
            $shortfall = $amountInCents - $walletBalance;
            $shortfallKES = $shortfall / 100;

            // If wallet has some funds, use them and request STK for the rest
            if ($walletBalance > 0) {
                try {
                    DB::transaction(function () use ($loan, $borrower, $borrowerWallet, $amountInCents, $walletBalance) {
                        $lender = $loan->lender;
                        $loanRequest = $loan->loanRequest;

                        // Use all available wallet balance
                        $borrowerWallet->decrement('balance', $walletBalance);

                        // Calculate proportional interest and principal for wallet portion
                        $totalRepayable = $loan->total_repayable;
                        $principalAmount = $loan->principal_amount;
                        $interestAmount = $totalRepayable - $principalAmount;

                        $walletRatio = $walletBalance / $amountInCents;
                        $walletPrincipalRepaid = $principalAmount * $walletRatio;
                        $walletInterestRepaid = $interestAmount * $walletRatio;

                        // Update loan with wallet portion
                        $newAmountRepaid = $loan->amount_repaid + $walletBalance;
                        $loan->update([
                            'amount_repaid' => $newAmountRepaid,
                            'status' => $newAmountRepaid >= $totalRepayable ? 'repaid' : 'active',
                        ]);

                        // Credit lender with wallet portion
                        $lender->wallet->increment('balance', $walletBalance);

                        // If fully repaid with wallet, release collateral
                        if ($newAmountRepaid >= $totalRepayable) {
                            $borrowerWallet->increment('balance', $loanRequest->collateral_locked);
                            $loanRequest->update(['status' => 'repaid']);

                            $newReputation = min(100, $borrower->reputation_score + 10);
                            $borrower->update(['reputation_score' => $newReputation]);
                        } else {
                            // Partial reputation increase
                            $reputationIncrease = (int) (($walletBalance / $totalRepayable) * 5);
                            $newReputation = min(100, $borrower->reputation_score + $reputationIncrease);
                            $borrower->update(['reputation_score' => $newReputation]);
                        }

                        // Log wallet transaction
                        $borrower->transactions()->create([
                            'transactionable_id' => $loan->id,
                            'transactionable_type' => Loan::class,
                            'type' => 'partial_repayment',
                            'amount' => -$walletBalance,
                            'status' => 'successful',
                        ]);

                        $lender->transactions()->create([
                            'type' => 'loan_repayment_credit',
                            'amount' => $walletBalance,
                            'status' => 'successful',
                        ]);
                    });

                    // Now initiate STK push for the shortfall
                    $phoneNumber = preg_replace('/^0/', '254', $borrower->phone_number);
                    $channelId = config('payhero.channel_id');
                    $provider = config('payhero.provider', 'm-pesa');
                    $externalRef = 'REPAY_'.$loan->id.'_'.Str::random(8);

                    // Create pending transaction for STK portion
                    $transaction = $borrower->transactions()->create([
                        'transactionable_id' => $loan->id,
                        'transactionable_type' => Loan::class,
                        'type' => 'stk_repayment',
                        'amount' => -$shortfall,
                        'status' => 'pending',
                        'payhero_transaction_id' => $externalRef,
                    ]);

                    // Dispatch STK push job
                    $payload = [
                        'amount' => $shortfallKES,
                        'phone_number' => $phoneNumber,
                        'channel_id' => $channelId,
                        'provider' => $provider,
                        'callback_url' => url('/api/webhooks/payhero'),
                        'external_reference' => $externalRef,
                    ];

                    InitiatePayHeroPayment::dispatch($transaction, $payload);

                    return back()->with('success', 'Insufficient funds! Used KES '.number_format($walletBalance / 100, 2).' from wallet. Please enter your M-Pesa PIN to add KES '.number_format($shortfallKES, 2).' and complete the repayment.');

                } catch (\Throwable $e) {
                    Log::error("Smart repayment failed for Loan #{$loan->id}", ['error' => $e->getMessage()]);

                    return back()->with('error', 'An error occurred during smart repayment. Please try again.');
                }
            } else {
                // No wallet funds, initiate full STK push
                $phoneNumber = preg_replace('/^0/', '254', $borrower->phone_number);
                $channelId = config('payhero.channel_id');
                $provider = config('payhero.provider', 'm-pesa');
                $externalRef = 'REPAY_'.$loan->id.'_'.Str::random(8);

                // Create pending transaction
                $transaction = $borrower->transactions()->create([
                    'transactionable_id' => $loan->id,
                    'transactionable_type' => Loan::class,
                    'type' => 'repayment',
                    'amount' => -$amountInCents,
                    'status' => 'pending',
                    'payhero_transaction_id' => $externalRef,
                ]);

                // Dispatch STK push job
                $payload = [
                    'amount' => $validated['amount'],
                    'phone_number' => $phoneNumber,
                    'channel_id' => $channelId,
                    'provider' => $provider,
                    'callback_url' => url('/api/webhooks/payhero'),
                    'external_reference' => $externalRef,
                ];

                InitiatePayHeroPayment::dispatch($transaction, $payload);

                return back()->with('success', 'Insufficient funds! Please enter your M-Pesa PIN to complete the repayment.');
            }
        }

        try {
            DB::transaction(function () use ($loan, $borrower, $borrowerWallet, $amountInCents) {
                $lender = $loan->lender;
                $loanRequest = $loan->loanRequest;

                // Calculate proportional interest and principal
                $totalRepayable = $loan->total_repayable;
                $principalAmount = $loan->principal_amount;
                $interestAmount = $totalRepayable - $principalAmount;

                $repaymentRatio = $amountInCents / $totalRepayable;
                $principalRepaid = $principalAmount * $repaymentRatio;
                $interestRepaid = $interestAmount * $repaymentRatio;

                // 1. Debit the Borrower's wallet
                $borrowerWallet->decrement('balance', $amountInCents);

                // 2. Credit the Lender's wallet
                $lender->wallet->increment('balance', $amountInCents);

                // 3. Update loan with partial repayment
                $newAmountRepaid = $loan->amount_repaid + $amountInCents;
                $loan->update([
                    'amount_repaid' => $newAmountRepaid,
                    'status' => $newAmountRepaid >= $totalRepayable ? 'repaid' : 'active',
                ]);

                // 4. If fully repaid, release collateral and update loan request
                if ($newAmountRepaid >= $totalRepayable) {
                    $borrowerWallet->increment('balance', $loanRequest->collateral_locked);
                    $loanRequest->update(['status' => 'repaid']);

                    // Increase borrower's reputation for full repayment (capped at 100)
                    $newReputation = min(100, $borrower->reputation_score + 10);
                    $borrower->update(['reputation_score' => $newReputation]);
                } else {
                    // Partial reputation increase for partial repayment (capped at 100)
                    $reputationIncrease = (int) ($repaymentRatio * 5); // Max 5 points for partial
                    $newReputation = min(100, $borrower->reputation_score + $reputationIncrease);
                    $borrower->update(['reputation_score' => $newReputation]);
                }

                // 5. Log transactions
                $borrower->transactions()->create([
                    'transactionable_id' => $loan->id,
                    'transactionable_type' => Loan::class,
                    'type' => 'partial_repayment',
                    'amount' => -$amountInCents,
                    'status' => 'successful',
                ]);

                $lender->transactions()->create([
                    'type' => 'loan_repayment_credit',
                    'amount' => $amountInCents,
                    'status' => 'successful',
                ]);

                if ($newAmountRepaid >= $totalRepayable) {
                    $borrower->transactions()->create([
                        'type' => 'collateral_release',
                        'amount' => $loanRequest->collateral_locked,
                        'status' => 'successful',
                    ]);
                }
            });

            $message = $loan->fresh()->status === 'repaid'
                ? 'Loan fully repaid! Your collateral has been released.'
                : 'Partial repayment successful! You can make additional payments anytime.';

            return back()->with('success', $message);

        } catch (\Throwable $e) {
            Log::error("Partial repayment failed for Loan #{$loan->id}", ['error' => $e->getMessage()]);

            return back()->with('error', 'An error occurred during repayment. Please try again.');
        }
    }
}
