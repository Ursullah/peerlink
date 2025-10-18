<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction; // <-- Import the Transaction model
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of all transactions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 1. Get all transactions with the user and base transactionable model
        $transactions = Transaction::with([
                            'user', // The user who owns the transaction
                            'transactionable' // The related model (Loan, LoanRequest, etc.)
                        ])
                        ->latest()       // Order by newest
                        ->paginate(20);  // Show 20 per page

        // 2. Conditionally load nested relations ONLY for the models that have them.
        // This avoids the N+1 problem and fixes your error.
        $transactions->loadMorph('transactionable', [
            Loan::class => ['lender', 'borrower'],
            LoanRequest::class => ['borrower'] // Or [] if LoanRequest has no borrower
        ]);

        return view('admin.transactions.index', compact('transactions'));
    }
}