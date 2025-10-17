<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lender Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             {{-- Success/Error Messages --}}
            @if (session('success')) <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><span class="block sm:inline">{{ session('success') }}</span></div> @endif
            @if (session('error')) <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><strong class="font-bold">Error!</strong><span class="block sm:inline">{{ session('error') }}</span></div> @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invested</h3>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">KES {{ number_format($stats['total_invested'] / 100, 2) }}</p>
                </div>
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Returned</h3>
                    <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">KES {{ number_format($stats['total_returned'] / 100, 2) }}</p>
                </div>
                 <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Earned</h3>
                    <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">KES {{ number_format($stats['profit_earned'] / 100, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Investments</h3>
                    <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['active_investments'] }}</p>
                     <a href="{{ route('lender.loans.investments') }}" class="text-sm text-indigo-600 hover:underline">View All &rarr;</a>
                </div>
            </div>

            {{-- Main Content Grid --}}
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                 {{-- Recent Activity --}}
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Wallet Activity</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentTransactions as $transaction)
                            <li class="py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ str_replace('_', ' ', ucfirst($transaction->type)) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold @if($transaction->amount < 0) text-red-500 @else text-green-500 @endif">
                                            KES {{ number_format(abs($transaction->amount / 100), 2) }}
                                        </p>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($transaction->status == 'pending') bg-yellow-100 text-yellow-800 @endif
                                            @if($transaction->status == 'successful') bg-green-100 text-green-800 @endif
                                            @if($transaction->status == 'failed') bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>
                                @if($transaction->status == 'failed' && $transaction->failure_reason)
                                     <p class="mt-1 text-xs text-red-500">Reason: {{ $transaction->failure_reason }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="py-3 text-center text-gray-500 dark:text-gray-400">No recent activity.</li>
                        @endforelse
                    </ul>
                </div>

                 {{-- Investment Status Chart --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                     <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Investment Status Breakdown</h3>
                     <div class="max-w-sm mx-auto"> {{-- Limit chart width --}}
                         <canvas id="loanStatusChart"></canvas>
                     </div>
                </div>
            </div>

        </div>
    </div>

{{-- Script needed for Chart.js --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctxPie = document.getElementById('loanStatusChart')?.getContext('2d');
        const pieData = @json($pieChartData);

        if (ctxPie && pieData && pieData.labels.length > 0) {
             // Define colors for pie chart segments
            const pieColors = {
                'Active': 'rgba(59, 130, 246, 0.7)', // blue-500
                'Repaid': 'rgba(34, 197, 94, 0.7)', // green-500
                'Defaulted': 'rgba(239, 68, 68, 0.7)', // red-500
                // Add more statuses and colors if needed
            };
            const backgroundColors = pieData.labels.map(label => pieColors[label] || '#9CA3AF'); // Use gray as fallback

            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: pieData.labels,
                    datasets: [{
                        label: 'Loan Statuses',
                        data: pieData.data,
                        backgroundColor: backgroundColors,
                        borderColor: document.documentElement.classList.contains('dark') ? '#4B5563' : '#FFFFFF', // gray-600 or white
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                             labels: {
                                color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151', // gray-300 or gray-700
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } else if(ctxPie) {
             // Optional: Display a message if there's no data
            ctxPie.font = "16px Figtree, sans-serif";
            ctxPie.fillStyle = document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280';
            ctxPie.textAlign = "center";
            ctxPie.fillText("No investment data available yet.", ctxPie.canvas.width / 2, ctxPie.canvas.height / 2);
        }
    });
</script>
@endpush

</x-app-layout>