<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\User;
use App\Services\PlatformRevenueService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $revenueService = new PlatformRevenueService;

        $stats = [
            'total_users' => User::count(),
            'total_loans_funded' => Loan::count(),
            'total_money_lent' => Loan::sum('principal_amount'),
            'pending_loan_requests' => \App\Models\LoanRequest::where('status', 'pending_approval')->count(),
        ];

        // Add revenue statistics
        $revenueStats = $revenueService->getRevenueStats();
        $revenueBreakdown = $revenueService->getRevenueBreakdown();
        $monthlyRevenue = $revenueService->getMonthlyRevenueTrend(6);

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

        // Revenue chart data
        $revenueChartData = [
            'labels' => $monthlyRevenue->map(fn ($item) => $item->year.'-'.str_pad($item->month, 2, '0', STR_PAD_LEFT)),
            'data' => $monthlyRevenue->pluck('total_amount'),
        ];

        // Revenue breakdown for pie chart
        $revenuePieData = [
            'labels' => $revenueBreakdown->keys()->map(fn ($key) => str_replace('_', ' ', ucfirst($key))),
            'data' => $revenueBreakdown->values(),
            'colors' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
        ];
        // --- End of Chart Data Logic ---

        return view('admin.dashboard', compact(
            'stats',
            'recentTransactions',
            'users',
            'chartData',
            'revenueStats',
            'revenueBreakdown',
            'revenueChartData',
            'revenuePieData'
        ));
    }
}
