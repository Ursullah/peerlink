<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display a listing of the active loan requests.
     */
    public function index()
    {
        // Show only loans that are 'active' (approved by admin, ready for funding)
        $loanRequests = LoanRequest::where('status', 'active')->latest()->get();
        return view('lender.loans.index', compact('loanRequests'));
    }

    /**
     * Fund the specified loan request.
     */
    public function fund(LoanRequest $loanRequest)
    {
        $lender = Auth::user();
        $lenderWallet = $lender->wallet;
        $loanAmount = $loanRequest->amount;

        // 1. Check if lender has enough funds
        if ($lenderWallet->balance < $loanAmount) {
            return back()->with('error', 'Your wallet balance is insufficient to fund this loan.');
        }

        // 2. Use a database transaction for safety
        DB::transaction(function () use ($loanRequest, $lender, $lenderWallet, $loanAmount) {
            $borrower = $loanRequest->borrower;
            $borrowerWallet = $borrower->wallet;

            // 3. Debit the lender's wallet
            $lenderWallet->balance -= $loanAmount;
            $lenderWallet->save();

            // 4. Credit the borrower's wallet
            $borrowerWallet->balance += $loanAmount;
            $borrowerWallet->save();

            // 5. Update the loan request status to 'funded'
            $loanRequest->update(['status' => 'funded']);

            // 6. Create the official Loan record
            $interestAmount = $loanAmount * ($loanRequest->interest_rate / 100);
            Loan::create([
                'loan_request_id' => $loanRequest->id,
                'borrower_id' => $borrower->id,
                'lender_id' => $lender->id,
                'principal_amount' => $loanAmount,
                'interest_amount' => $interestAmount,
                'total_repayable' => $loanAmount + $interestAmount,
                'due_date' => Carbon::now()->addDays($loanRequest->repayment_period),
            ]);

            // 7. Log transactions for both parties
            Transaction::create([
                'user_id' => $lender->id,
                'type' => 'loan_funding',
                'amount' => -$loanAmount,
                'status' => 'successful',
            ]);
            Transaction::create([
                'user_id' => $borrower->id,
                'type' => 'deposit',
                'amount' => $loanAmount,
                'status' => 'successful',
            ]);
        });

        return back()->with('success', 'Loan funded successfully! The amount has been transferred to the borrower.');
    }
}