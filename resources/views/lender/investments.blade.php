<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Investments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Page Title --}}
            <h3 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-gray-100">
                Active Investments ({{ $myLoans->count() }})
            </h3>

            {{-- Investment Cards Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                @forelse ($myLoans as $loan)
                    {{-- Calculate Progress --}}
                    @php
                        $repaid = $loan->amount_repaid ?? 0;
                        $total = $loan->total_repayable;
                        $progressPercent = $total > 0 ? ($repaid / $total) * 100 : 0;
                    @endphp

                    {{-- Investment Card --}}
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            {{-- Card Header --}}
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $loan->borrower->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $loan->loanRequest->reason ?? 'Loan' }}</p>
                                </div>
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if ($loan->status == 'active') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif
                                    @if ($loan->status == 'repaid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif
                                    @if ($loan->status == 'defaulted') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </div>

                            {{-- Loan Details Grid --}}
                            <div class="grid grid-cols-3 gap-y-4 gap-x-2 text-sm mb-5">
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Your Principal</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">KES
                                        {{ number_format($loan->principal_amount / 100, 2) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Your Repayment</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">KES
                                        {{ number_format($loan->total_repayable / 100, 2) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Terms</span>
                                    <span
                                        class="font-medium text-gray-800 dark:text-gray-200">{{ $loan->loanRequest->interest_rate ?? '0' }}%
                                        interest, {{ $loan->loanRequest->repayment_period ?? '0' }} days</span>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Repayment
                                        Progress</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        KES {{ number_format($repaid / 100, 2) }} / KES
                                        {{ number_format($total / 100, 2) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-green-600 h-2.5 rounded-full"
                                        style="width: {{ $progressPercent }}%"></div>
                                </div>
                            </div>

                            {{-- Card Footer --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 mt-5 pt-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">
                                        Due: {{ \Carbon\Carbon::parse($loan->due_date)->format('d/m/Y') }}
                                    </span>
                                    <span class="text-green-600 dark:text-green-400 font-semibold">
                                        Your Interest: KES {{ number_format($loan->interest_amount / 100, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center text-gray-500 dark:text-gray-400">
                        You have not funded any loans yet.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
