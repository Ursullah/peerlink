<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformRevenueService;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    protected $revenueService;

    public function __construct(PlatformRevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * Display platform revenue dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = $this->revenueService->getRevenueStats();
        $breakdown = $this->revenueService->getRevenueBreakdown($startDate, $endDate);
        $monthlyTrend = $this->revenueService->getMonthlyRevenueTrend(12);

        return view('admin.revenue.index', compact('stats', 'breakdown', 'monthlyTrend', 'startDate', 'endDate'));
    }

    /**
     * Get revenue data for charts
     */
    public function chartData(Request $request)
    {
        $period = $request->get('period', 'monthly');

        if ($period === 'monthly') {
            $data = $this->revenueService->getMonthlyRevenueTrend(12);
        } else {
            // Weekly data for last 12 weeks
            $data = $this->revenueService->getMonthlyRevenueTrend(3); // Simplified for demo
        }

        return response()->json($data);
    }
}
