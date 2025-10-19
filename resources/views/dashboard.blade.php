<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Session Messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Reputation Score</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $stats['reputation_score'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Loans</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $stats['active_loan_count'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Borrowed</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">KES
                        {{ number_format($stats['total_borrowed'] / 100, 2) }}</p>
                </div>
            </div>

            {{-- Main Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Wallet Card --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold">My Wallet</h3>
                            <p class="mt-2">
                                Your current balance is: <span class="font-bold text-xl">KES
                                    {{ number_format(Auth::user()->wallet->balance / 100, 2) }}</span>
                            </p>
                            <div class="mt-4 flex flex-col space-y-2">
                                <x-primary-button :href="route('loan-requests.create')" class="justify-center">Request a New
                                    Loan</x-primary-button>
                                <div class="flex space-x-2">
                                    <a href="{{ route('wallet.deposit.form') }}"
                                        class="w-full text-center inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">Top-Up</a>
                                    <a href="{{ route('wallet.withdraw.form') }}"
                                        class="w-full text-center inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">Withdraw</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Recent Activity Card --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Activity</h3>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($recentTransactions as $transaction)
                                <li class="py-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-800 dark:text-gray-200">
                                                {{ str_replace('_', ' ', ucfirst($transaction->type)) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $transaction->created_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-sm font-semibold @if ($transaction->amount < 0) text-red-500 @else text-green-500 @endif">
                                                KES {{ number_format(abs($transaction->amount / 100), 2) }}</p>
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full @if ($transaction->status == 'pending') bg-yellow-100 text-yellow-800 @elseif($transaction->status == 'successful') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">{{ ucfirst($transaction->status) }}</span>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="py-3 text-center text-gray-500 dark:text-gray-400">No recent activity.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">My Loan Requests</h3>
                        </div>

                        {{-- NEW: Loan Request Cards --}}
                        <div class="space-y-4">
                            @forelse ($loanRequests as $request)
                                @php
                                    $funded = $request->loans->sum('principal_amount');
                                    $total = $request->amount;
                                    $progressPercent = $total > 0 ? ($funded / $total) * 100 : 0;
                                @endphp
                                <div class="border rounded-lg p-4 dark:border-gray-700">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-lg">KES
                                                {{ number_format($request->amount / 100, 2) }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $request->interest_rate }}% interest ・
                                                {{ $request->repayment_period }} days
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if ($request->status == 'pending_approval') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif
                                                @if ($request->status == 'active') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif
                                                @if ($request->status == 'funded' || $request->status == 'repaid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif
                                                @if ($request->status == 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                                {{ str_replace('_', ' ', $request->status) }}
                                            </span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $request->created_at->format('d M, Y') }}</p>
                                        </div>
                                    </div>

                                    {{-- Progress Bar and Repay Button --}}
                                    @if ($request->status == 'active' || $request->status == 'funded')
                                        <div class="mt-4">
                                            @if ($request->status == 'active')
                                                <div class="flex justify-between mb-1">
                                                    <span
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Funding
                                                        Progress</span>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        {{ number_format($progressPercent, 0) }}%
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-600">
                                                    <div class="bg-blue-600 h-2.5 rounded-full"
                                                        style="width: {{ $progressPercent }}%"></div>
                                                </div>
                                            @endif

                                            @if ($request->status == 'funded' && $request->loans->where('status', 'active')->isNotEmpty())
                                                <div class="mt-4 flex justify-end">
                                                    <form method="POST"
                                                        action="{{ route('loan-requests.repay', $request) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                                            Repay Loan
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                    You have not made any loan requests yet.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
