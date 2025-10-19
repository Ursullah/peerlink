<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Fetch lender's loans
        $myLoans = Loan::where('lender_id', $userId)->get();

        // Calculate Stats
        $stats = [
            'total_invested' => $myLoans->sum('principal_amount'),
            'total_returned' => $myLoans->where('status', 'repaid')->sum('total_repayable'),
            'active_investments' => $myLoans->where('status', 'active')->count(),
            'profit_earned' => $myLoans->where('status', 'repaid')->sum('interest_amount'),
            'pending_interest' => $myLoans->where('status', 'active')->sum('interest_amount'),
        ];

        // Fetch Recent Transactions
        $recentTransactions = $user->transactions()->latest()->take(5)->get();

        // Chart Data: Loan Status Breakdown (Pie Chart)
        $statusCounts = $myLoans->groupBy('status')->map->count();
        $pieChartData = [
            'labels' => $statusCounts->keys()->map(fn ($status) => ucfirst($status)),
            'data' => $statusCounts->values(),
        ];

        return view('lender.dashboard', compact('stats', 'recentTransactions', 'pieChartData'));
    }
}
