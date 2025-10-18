<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Loans Funded</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $stats['total_loans_funded'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Money Lent</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">KES
                        {{ number_format($stats['total_money_lent'] / 100, 2) }}</p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-800/20 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending Requests</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-800 dark:text-yellow-200">
                        {{ $stats['pending_loan_requests'] }}</p>
                    <a href="{{ route('admin.loans.index') }}" class="text-sm text-yellow-600 hover:underline">View
                        Requests &rarr;</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg shadow-sm text-white">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-100">Total Revenue</p>
                            <p class="text-2xl font-bold">{{ $revenueStats['formatted_total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg shadow-sm text-white">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-100">Interest Commission</p>
                            <p class="text-2xl font-bold">KES
                                {{ number_format(($revenueBreakdown['interest_commission'] ?? 0) / 100, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg shadow-sm text-white">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-100">Transaction Fees</p>
                            <p class="text-2xl font-bold">KES
                                {{ number_format(($revenueBreakdown['transaction_fee'] ?? 0) / 100, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 rounded-lg shadow-sm text-white">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-red-100">Late Fees</p>
                            <p class="text-2xl font-bold">KES
                                {{ number_format(($revenueBreakdown['late_fee'] ?? 0) / 100, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Monthly Revenue Trend</h3>
                    <div class="relative h-80">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Revenue Breakdown</h3>
                    <div class="relative h-80">
                        <canvas id="revenuePieChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Loans Funded Per Day</h3>
                <div class="relative h-80">
                    <canvas id="loansChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Transactions</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentTransactions as $transaction)
                            <li class="py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $transaction->user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ str_replace('_', ' ', ucfirst($transaction->type)) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-sm font-semibold @if ($transaction->amount < 0) text-red-500 @else text-green-500 @endif">
                                            KES {{ number_format(abs($transaction->amount / 100), 2) }}
                                        </p>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if ($transaction->status == 'pending') bg-yellow-100 text-yellow-800 @endif
                                            @if ($transaction->status == 'successful') bg-green-100 text-green-800 @endif
                                            @if ($transaction->status == 'failed') bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>
                                @if ($transaction->status == 'failed' && $transaction->failure_reason)
                                    <p class="mt-1 text-xs text-red-500">Reason: {{ $transaction->failure_reason }}</p>
                                @endif
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $transaction->created_at->format('d M, Y g:i A') }}</p>
                            </li>
                        @empty
                            <li class="py-3 text-center text-gray-500 dark:text-gray-400">No transactions yet.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">User List</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Name</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Phone</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Role</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Reputation</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Joined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr class="text-gray-900 dark:text-gray-300">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $user->name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $user->phone_number }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($user->role) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $user->reputation_score }}</td>
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $user->created_at->format('d M, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">No users
                                            found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Script --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#9CA3AF' : '#6B7280';

                // Loans Chart
                const loansCtx = document.getElementById('loansChart')?.getContext('2d');
                if (loansCtx) {
                    const chartData = @json($chartData);
                    new Chart(loansCtx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Loans Funded',
                                data: chartData.data,
                                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                                borderColor: 'rgba(79, 70, 229, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // This is correct
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: textColor
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: textColor
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: textColor
                                    }
                                }
                            }
                        }
                    });
                }

                // Revenue Trend Chart
                const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
                if (revenueCtx) {
                    const revenueData = @json($revenueChartData);
                    new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: revenueData.labels,
                            datasets: [{
                                label: 'Revenue (KES)',
                                data: revenueData.data.map(amount => amount / 100),
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 2,
                                borderRadius: 4,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // This is correct
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: textColor,
                                        callback: function(value) {
                                            return 'KES ' + value.toLocaleString();
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: textColor
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: textColor
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Revenue: KES ' + context.parsed.y.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Revenue Pie Chart
                const pieCtx = document.getElementById('revenuePieChart')?.getContext('2d');
                if (pieCtx) {
                    const pieData = @json($revenuePieData);
                    new Chart(pieCtx, {
                        type: 'doughnut',
                        data: {
                            labels: pieData.labels,
                            datasets: [{
                                data: pieData.data.map(amount => amount / 100),
                                backgroundColor: pieData.colors,
                                borderColor: pieData.colors,
                                borderWidth: 2,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // This is correct
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: textColor,
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return context.label + ': KES ' + context.parsed
                                            .toLocaleString() + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>