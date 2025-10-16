<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="0">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="80">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Loans Funded</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_loans_funded'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="160">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Money Lent</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">KES {{ number_format($stats['total_money_lent'] / 100, 2) }}</p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-800/20 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="240">
                    <h3 class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending Requests</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-800 dark:text-yellow-200">{{ $stats['pending_loan_requests'] }}</p>
                    <a href="{{ route('admin.loans.index') }}" class="text-sm text-yellow-600 hover:underline">View Requests &rarr;</a>
                </div>
            </div>
            <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="0">
                <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Loans Funded Per Day</h3>
                <canvas id="loansChart"></canvas>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Transactions</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentTransactions as $transaction)
                            <li class="py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $transaction->user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $transaction->type) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold @if($transaction->amount < 0) text-red-500 @else text-green-500 @endif">
                                            KES {{ number_format(abs($transaction->amount / 100), 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reputation</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($users as $user)
                                    <tr class="text-gray-900 dark:text-gray-300">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $user->name }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $user->phone_number }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($user->role) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $user->reputation_score }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->created_at->format('d M, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('loansChart').getContext('2d');
        const chartData = @json($chartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Loans Funded',
                    data: chartData.data,
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                             color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        }
                    }
                }
            }
        });
    });
</script>