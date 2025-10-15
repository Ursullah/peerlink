<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of loan requests.
     */
    public function index()
    {
        $loanRequests = LoanRequest::where('status', 'pending_approval')->latest()->get();
        return view('admin.loans.index', compact('loanRequests'));
    }

    /**
     * Approve the specified loan request.
     */
    public function approve(LoanRequest $loanRequest)
    {
        $loanRequest->update(['status' => 'active']);
        return back()->with('success', 'Loan request has been approved and is now visible to lenders.');
    }

    /**
     * Reject the specified loan request.
     */
    public function reject(LoanRequest $loanRequest)
    {
        DB::transaction(function () use ($loanRequest) {
            $borrower = $loanRequest->borrower;
            $wallet = $borrower->wallet;

            // Mark the request as rejected
            $loanRequest->update(['status' => 'rejected']);

            // Refund the locked collateral to the borrower's wallet
            $wallet->balance += $loanRequest->collateral_locked;
            $wallet->save();

            // Create a transaction record for the collateral release
            Transaction::create([
                'user_id' => $borrower->id,
                'transactionable_id' => $loanRequest->id,
                'transactionable_type' => LoanRequest::class,
                'type' => 'collateral_release',
                'amount' => $loanRequest->collateral_locked, // Positive amount for credit
                'status' => 'successful',
            ]);
        });

        return back()->with('success', 'Loan request has been rejected and collateral has been refunded.');
    }
}