<?php

namespace App\Http\Controllers;
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
            'interest_rate' => 'required|numeric|min:1|max:50',
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

            // Create the loan request
            $loanRequest = LoanRequest::create([
                'user_id' => $user->id,
                'amount' => $amountInCents,
                'repayment_period' => $validated['repayment_period'],
                'interest_rate' => $validated['interest_rate'],
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
}