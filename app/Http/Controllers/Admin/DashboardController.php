<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function index()
{
    $stats = [
        'total_users' => User::count(),
        'total_loans_funded' => Loan::count(),
        'total_money_lent' => Loan::sum('principal_amount'),
        'pending_loan_requests' => \App\Models\LoanRequest::where('status', 'pending_approval')->count(),
    ];

    $recentTransactions = \App\Models\Transaction::with('user')->latest()->take(10)->get();
    $users = User::latest()->get();

    // --- Chart Data Logic ---
    $loansByDay = Loan::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                        ->groupBy('date')
                        ->orderBy('date', 'asc')
                        ->pluck('count', 'date');

    $chartData = [
        'labels' => $loansByDay->keys(),
        'data' => $loansByDay->values(),
    ];
    // --- End of Chart Data Logic ---

    return view('admin.dashboard', compact('stats', 'recentTransactions', 'users', 'chartData'));
}
}