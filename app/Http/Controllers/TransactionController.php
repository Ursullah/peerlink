<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a paginated list of the user's transactions.
     */
    public function index()
    {
        $user = Auth::user();

        // Eager load the polymorphic relationship with specific nested relations for each type.
        $transactions = $user->transactions()
            ->with([
                'transactionable' => function ($morphTo) {
                    $morphTo->morphWith([
                        // When the transactionable model is a Loan, load its borrower and lender
                        Loan::class => ['borrower', 'lender'],
                        // When it's a LoanRequest, only load its borrower
                        LoanRequest::class => ['borrower'],
                    ]);
                }
            ])
            ->latest()
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }
}
