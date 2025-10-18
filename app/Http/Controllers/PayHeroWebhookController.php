<?php

namespace App\Http\Controllers;

use App\Jobs\InitiatePayHeroPayment;
use App\Models\LoanRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoanRequestController extends Controller
{
    // ... create() and store() methods remain unchanged ...
    public function create()
    {
        return view('loan-requests.create');
    }

    public function store(Request $request)
    {
        // 1. Validate the form data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100', // Minimum KES 100
            'repayment_period' => 'required|integer|min:7', // Minimum 7 days
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        // 2. Calculate required collateral (e.g., 20% of the loan amount)
        $amountInCents = $validated['amount'] * 100;
        $collateralRatio = 0.20; // 20%
        $requiredCollateral = $amountInCents * $collateralRatio;

        // 3. Check if the user's wallet has enough balance for the collateral
        if ($wallet->balance < $requiredCollateral) {
            return back()->withErrors(['amount' => 'Your wallet balance is insufficient to cover the required 20% collateral. Please top up your wallet.'])->withInput();
        }

        // 4. Use a database transaction to ensure data integrity
        DB::transaction(function () use ($user, $wallet, $validated, $amountInCents, $requiredCollateral) {
            $wallet->balance -= $requiredCollateral;
            $wallet->save();
            $systemInterestRate = 12.5;
            $loanRequest = LoanRequest::create([
                'user_id' => $user->id,
                'amount' => $amountInCents,
                'repayment_period' => $validated['repayment_period'],
                'interest_rate' => $systemInterestRate,
                'reason' => $validated['reason'],
                'collateral_locked' => $requiredCollateral,
                'status' => 'pending_approval',
            ]);
            Transaction::create([
                'user_id' => $user->id,
                'transactionable_id' => $loanRequest->id,
                'transactionable_type' => LoanRequest::class,
                'type' => 'collateral_lock',
                'amount' => -$requiredCollateral,
                'status' => 'successful',
            ]);
        });

        return redirect()->route('dashboard')->with('success', 'Your loan request has been submitted successfully and is pending approval!');
    }

    /**
     * Process the repayment of a specific loan request, paying back all lenders.
     * This method now handles both Wallet and STK Push logic.
     */
    public function repay(LoanRequest $loanRequest)
    {
        if ($loanRequest->user_id !== auth()->id() || $loanRequest->status !== 'funded') {
            abort(403, 'This action is unauthorized or the loan is not ready for repayment.');
        }

        $borrower = $loanRequest->borrower;
        $borrowerWallet = $borrower->wallet;
        $allLoans = $loanRequest->loans;
        $totalToRepayAllLenders = $allLoans->sum('total_repayable');

        // --- ATTEMPT WALLET REPAYMENT FIRST ---
        if ($borrowerWallet->balance >= $totalToRepayAllLenders) {
            Log::info("Processing instant wallet repayment for LoanRequest #{$loanRequest->id}");

            DB::transaction(function () use ($loanRequest, $totalToRepayAllLenders) {
                // This is the multi-lender repayment logic we built before
                $this->executeMultiLenderRepayment($loanRequest, $totalToRepayAllLenders);
            });

            return redirect()->route('dashboard')->with('success', 'Loan successfully repaid from your wallet!');
        }

        // --- FALLBACK: INITIATE STK PUSH ---
        Log::info("Wallet balance insufficient for LoanRequest #{$loanRequest->id}. Initiating STK Push for full amount.");
        $amountKES = $totalToRepayAllLenders / 100;
        $phoneNumber = preg_replace('/^0/', '254', $borrower->phone_number);
        $externalRef = 'REPAY_REQ_'.$loanRequest->id.'_'.Str::random(8);

        // Create a single pending transaction linked to the ENTIRE LoanRequest
        $transaction = $borrower->transactions()->create([
            'transactionable_id' => $loanRequest->id,
            'transactionable_type' => LoanRequest::class,
            'type' => 'repayment',
            'amount' => -$totalToRepayAllLenders,
            'status' => 'pending',
            'payhero_transaction_id' => $externalRef,
        ]);

        // Dispatch the job to call PayHero API
        $payload = [
            'amount' => $amountKES,
            'phone_number' => $phoneNumber,
            'channel_id' => config('payhero.channel_id'),
            'provider' => config('payhero.provider', 'm-pesa'),
            'callback_url' => url('/api/webhooks/payhero'), // Your webhook URL
            'external_reference' => $externalRef,
        ];

        InitiatePayHeroPayment::dispatch($transaction, $payload);

        return back()->with('success', 'Repayment initiated. Please check your phone and enter your M-Pesa PIN.');
    }

    /**
     * A private helper function containing the core multi-lender repayment logic.
     * This can now be called from both the wallet repayment and the webhook.
     */
    private function executeMultiLenderRepayment(LoanRequest $loanRequest, int $totalToRepay)
    {
        $borrower = $loanRequest->borrower;
        $borrowerWallet = $borrower->wallet;
        $allLoans = $loanRequest->loans;

        // 1. Debit Borrower's wallet (if it's a wallet payment)
        // Note: For STK push, this debit is conceptual as the money comes from M-Pesa
        if ($borrowerWallet->balance >= $totalToRepay) {
            $borrowerWallet->decrement('balance', $totalToRepay);
        }

        // 2. Loop through each partial loan and pay back the respective lender
        foreach ($allLoans as $loan) {
            $lender = $loan->lender;
            $lender->wallet->increment('balance', $loan->total_repayable);
            $loan->update(['status' => 'repaid', 'amount_repaid' => $loan->total_repayable]);
            Transaction::create(['user_id' => $lender->id, 'type' => 'loan_repayment_credit', 'amount' => $loan->total_repayable, 'status' => 'successful']);
        }

        // 3. Update main LoanRequest status
        $loanRequest->update(['status' => 'repaid']);

        // 4. Release Borrower's collateral
        $borrowerWallet->increment('balance', $loanRequest->collateral_locked);

        // 5. Increase borrower's reputation (capped at 100)
        $newReputation = min(100, $borrower->reputation_score + 10);
        $borrower->update(['reputation_score' => $newReputation]);

        // 6. Log transactions
        Transaction::create(['user_id' => $borrower->id, 'transactionable_id' => $loanRequest->id, 'transactionable_type' => LoanRequest::class, 'type' => 'repayment', 'amount' => -$totalToRepay, 'status' => 'successful']);
        Transaction::create(['user_id' => $borrower->id, 'type' => 'collateral_release', 'amount' => $loanRequest->collateral_locked, 'status' => 'successful']);
    }
}
