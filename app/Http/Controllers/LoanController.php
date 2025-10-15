<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Process the repayment of a specific loan.
     */
    public function repay(Loan $loan)
    {
        // Security Check: Ensure the person repaying is the actual borrower.
        if ($loan->borrower_id !== auth()->id()) {
            abort(403);
        }

        // === PAYHERO STK PUSH SIMULATION ===
        // In a real application, you would make an API call to PayHero here
        // to trigger an STK push for the $loan->total_repayable amount.
        // The rest of this code would then be moved to a webhook handler
        // that runs only after PayHero confirms the payment was successful.
        // For our MVP, we will assume the payment is instantly successful.
        
        DB::transaction(function () use ($loan) {
            $borrower = $loan->borrower;
            $lender = $loan->lender;
            $loanRequest = $loan->loanRequest;

            // 1. Credit the Lender's wallet with the principal + interest
            $lender->wallet->balance += $loan->total_repayable;
            $lender->wallet->save();
            
            // 2. Release the Borrower's collateral back to their wallet
            $borrower->wallet->balance += $loanRequest->collateral_locked;
            $borrower->wallet->save();

            // 3. Update the loan status to 'repaid'
            $loan->update(['status' => 'repaid', 'amount_repaid' => $loan->total_repayable]);
            $loanRequest->update(['status' => 'repaid']);

            // 4. Increase the borrower's reputation score
            $borrower->reputation_score += 10; // Award 10 points for on-time repayment
            $borrower->save();

            // 5. Log all transactions
            // Repayment from borrower's perspective (outflow handled by PayHero)
            Transaction::create(['user_id' => $borrower->id, 'type' => 'repayment', 'amount' => -$loan->total_repayable]);
            // Repayment received by lender
            Transaction::create(['user_id' => $lender->id, 'type' => 'deposit', 'amount' => $loan->total_repayable]);
            // Collateral release for borrower
            Transaction::create(['user_id' => $borrower->id, 'type' => 'collateral_release', 'amount' => $loanRequest->collateral_locked]);
        });
        
        return back()->with('success', 'Thank you! Your loan has been successfully repaid.');
    }
}