<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanRequestController extends Controller
{
    /**
     * Show the form for creating a new loan request.
     */
    public function create()
    {
        return view('loan-requests.create');
    }

    /**
     * Store a newly created loan request in storage.
     */
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
        // We store amounts in cents, so multiply by 100
        $amountInCents = $validated['amount'] * 100;
        $collateralRatio = 0.20; // 20%
        $requiredCollateral = $amountInCents * $collateralRatio;

        // 3. Check if the user's wallet has enough balance for the collateral
        if ($wallet->balance < $requiredCollateral) {
            return back()->withErrors(['amount' => 'Your wallet balance is insufficient to cover the required 20% collateral. Please top up your wallet.'])->withInput();
        }

        // 4. Use a database transaction to ensure data integrity
        DB::transaction(function () use ($user, $wallet, $validated, $amountInCents, $requiredCollateral) {
            // "Lock" the collateral by deducting it from the wallet balance
            $wallet->balance -= $requiredCollateral;
            $wallet->save();

            $systemInterestRate = 12.5; // Fixed system interest rate

            // Create the loan request
            $loanRequest = LoanRequest::create([
                'user_id' => $user->id,
                'amount' => $amountInCents,
                'repayment_period' => $validated['repayment_period'],
                'interest_rate' => $systemInterestRate,
                'reason' => $validated['reason'],
                'collateral_locked' => $requiredCollateral,
                'status' => 'pending_approval',
            ]);

            // Create a transaction record for the collateral lock
            Transaction::create([
                'user_id' => $user->id,
                'transactionable_id' => $loanRequest->id,
                'transactionable_type' => LoanRequest::class,
                'type' => 'collateral_lock',
                'amount' => -$requiredCollateral, // Negative amount for debit
                'status' => 'successful',
            ]);
        });

        // 5. Redirect to the dashboard with a success message
        return redirect()->route('dashboard')->with('success', 'Your loan request has been submitted successfully and is pending approval!');
    }

    /**
     * Process the repayment of a specific loan request, paying back all lenders.
     */
    public function repay(LoanRequest $loanRequest)
    {
        // Security Check: Ensure the user owns this request and it's ready for repayment
        if ($loanRequest->user_id !== auth()->id() || $loanRequest->status !== 'funded') {
            abort(403, 'This action is unauthorized or the loan is not ready for repayment.');
        }

        $borrower = $loanRequest->borrower;
        $borrowerWallet = $borrower->wallet;
        $allLoans = $loanRequest->loans; // Get all partial loans associated with this request

        // Calculate the total sum needed to repay all lenders
        $totalToRepayAllLenders = $allLoans->sum('total_repayable');

        // Check if the borrower's wallet has enough funds
        if ($borrowerWallet->balance < $totalToRepayAllLenders) {
            // Here you could add logic to trigger an STK Push if needed
            return back()->with('error', 'Your wallet balance is insufficient to repay the loan in full.');
        }

        // Use a transaction to ensure all operations succeed or none do
        DB::transaction(function () use ($loanRequest, $borrower, $borrowerWallet, $allLoans, $totalToRepayAllLenders) {
            // 1. Debit the Borrower's wallet for the full repayment amount
            $borrowerWallet->decrement('balance', $totalToRepayAllLenders);

            // 2. Loop through each partial loan and pay back the respective lender
            foreach ($allLoans as $loan) {
                $lender = $loan->lender;
                $lender->wallet->increment('balance', $loan->total_repayable);

                // Update the individual loan's status
                $loan->update(['status' => 'repaid', 'amount_repaid' => $loan->total_repayable]);

                // Log the transaction for the lender
                Transaction::create(['user_id' => $lender->id, 'type' => 'loan_repayment_credit', 'amount' => $loan->total_repayable, 'status' => 'successful']);
            }

            // 3. Update the main LoanRequest status to 'repaid'
            $loanRequest->update(['status' => 'repaid']);

            // 4. Release the Borrower's collateral back to their wallet
            $borrowerWallet->increment('balance', $loanRequest->collateral_locked);

            // 5. Increase the borrower's reputation score
            $borrower->increment('reputation_score', 10); // Or your desired value

            // 6. Log the main repayment and collateral release for the borrower
            Transaction::create(['user_id' => $borrower->id, 'transactionable_id' => $loanRequest->id, 'transactionable_type' => LoanRequest::class, 'type' => 'repayment', 'amount' => -$totalToRepayAllLenders, 'status' => 'successful']);
            Transaction::create(['user_id' => $borrower->id, 'type' => 'collateral_release', 'amount' => $loanRequest->collateral_locked, 'status' => 'successful']);
        });

        return redirect()->route('dashboard')->with('success', 'Loan successfully repaid!');
    }
}
