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
        
        $transactions = Transaction::with([
            'user', // The user who owns the transaction
            'transactionable', // The related model (Loan, LoanRequest, etc.)
            'transactionable.lender', // The lender on the loan
            'transactionable.borrower' // The borrower on the loan
        ])
        ->latest()       // Order by newest
        ->paginate(20);  // Show 20 per page

        return view('admin.transactions.index', compact('transactions'));
    }
}