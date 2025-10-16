<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div 
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => show = false, 4000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert"
                >
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div 
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => show = false, 4000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                    role="alert"
                >
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="0">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Reputation Score</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['reputation_score'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="80">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Loans</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active_loan_count'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm animate-on-scroll" data-delay="160">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Borrowed</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">KES {{ number_format($stats['total_borrowed'] / 100, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold">My Wallet</h3>
                            <p class="mt-2">
                                @if(Auth::user()->wallet)
                                    Your current balance is: <span class="font-bold text-xl">KES {{ number_format(Auth::user()->wallet->balance / 100, 2) }}</span>
                                @else
                                    <span class="text-red-500">Wallet not found.</span>
                                @endif
                            </p>
                            <div class="mt-4 flex flex-col space-y-2">
                                <x-primary-button :href="route('loan-requests.create')" class="justify-center">Request a New Loan</x-primary-button>
                                <div class="flex space-x-2">
                                    <a href="{{ route('wallet.deposit.form') }}" class="w-full text-center inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">Top-Up</a>
                                    <a href="{{ route('wallet.withdraw.form') }}" class="w-full text-center inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">Withdraw</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Activity</h3>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($recentTransactions as $transaction)
                                <li class="py-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', ucfirst($transaction->type)) }}</p>
                                        <p class="text-sm font-semibold @if($transaction->amount < 0) text-red-500 @else text-green-500 @endif">
                                            KES {{ number_format(abs($transaction->amount / 100), 2) }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                                </li>
                            @empty
                                <li class="py-3 text-center text-gray-500 dark:text-gray-400">No recent activity.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">My Loan Requests</h3>
                        <div class="overflow-x-auto">
                             <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Interest</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($loanRequests as $request)
                                        <tr class="text-gray-800 dark:text-gray-300">
                                            <td class="px-6 py-4 whitespace-nowrap">KES {{ number_format($request->amount / 100, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $request->interest_rate }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $request->repayment_period }} days</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($request->status == 'pending_approval') bg-yellow-100 text-yellow-800 @endif
                                                    @if($request->status == 'active') bg-blue-100 text-blue-800 @endif
                                                    @if($request->status == 'funded' || $request->status == 'repaid') bg-green-100 text-green-800 @endif
                                                    @if($request->status == 'rejected') bg-red-100 text-red-800 @endif">
                                                    {{ str_replace('_', ' ', $request->status) }}
                                                </span>
                                                @if($request->status == 'funded' && $request->loan && $request->loan->status == 'active')
                                                    <form method="POST" action="{{ route('loans.repay', $request->loan) }}" class="inline ml-2">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-0.5 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                                                            Repay
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $request->created_at->format('d M, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                                                You have not made any loan requests yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>