<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class TransactionController extends Controller
{
    /**
     * Display a paginated list of the user's transactions.
     */
    public function index()
{
    $user = Auth::user();
    // Eager load the 'transactionable' relationship and its nested relationships
    $transactions = $user->transactions()
                        ->with(['transactionable.borrower', 'transactionable.lender'])
                        ->latest()
                        ->paginate(15);
    return view('transactions.index', compact('transactions'));
}
}