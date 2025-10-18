<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Platform Revenue Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Revenue Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['formatted_total'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Interest Commission</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    KES {{ number_format(($breakdown['interest_commission'] ?? 0) / 100, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaction Fees</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    KES {{ number_format(($breakdown['transaction_fee'] ?? 0) / 100, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Late Fees</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    KES {{ number_format(($breakdown['late_fee'] ?? 0) / 100, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Breakdown Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Revenue Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">By Type</h4>
                            <div class="space-y-2">
                                @foreach($breakdown as $type => $amount)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">KES {{ number_format($amount / 100, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Monthly Trend</h4>
                            <div class="space-y-2">
                                @foreach($monthlyTrend->take(6) as $month)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $month->year }}-{{ str_pad($month->month, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">KES {{ number_format($month->total_amount / 100, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Statistics -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Revenue Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Top Revenue Source</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $stats['top_revenue_source']) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Average Monthly</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">KES {{ number_format($stats['average_monthly'] / 100, 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Growth Rate</p>
                            <p class="text-lg font-semibold text-green-600">+12.5%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
