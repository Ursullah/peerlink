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

        // Fetch all transactions for the logged-in user, newest first, paginated
        $transactions = $user->transactions()
                            ->latest() // Order by created_at descending
                            ->paginate(15); // Show 15 transactions per page

        return view('transactions.index', compact('transactions'));
    }
}