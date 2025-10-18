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
        // Get base transactions
        $transactions = Transaction::with(['user', 'transactionable'])
                        ->latest()
                        ->paginate(20);

        
        // Conditionally load nested relationships to avoid the error
        $transactions->loadMorph('transactionable', [
            Loan::class => ['lender', 'borrower'],
            LoanRequest::class => ['borrower'] // It will correctly load this one
        ]);

        return view('admin.transactions.index', compact('transactions'));
    }
}