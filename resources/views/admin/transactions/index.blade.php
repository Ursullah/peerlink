<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('All Transactions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Amount (KES)
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Details
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($transactions as $transaction)
                                    <tr class="text-gray-800 dark:text-gray-300">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $transaction->user->name ?? 'N/A' }}
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->user->email ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $transaction->created_at->format('d M, Y g:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ str_replace('_', ' ', ucfirst($transaction->type)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium @if ($transaction->amount < 0) text-red-500 @else text-green-500 @endif">
                                            {{ number_format(abs($transaction->amount / 100), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if ($transaction->status == 'pending') bg-yellow-100 text-yellow-800 @endif
                                                @if ($transaction->status == 'successful') bg-green-100 text-green-800 @endif
                                                @if ($transaction->status == 'failed') bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            @if ($transaction->status == 'failed' && $transaction->failure_reason)
                                                <span class="text-red-500">{{ $transaction->failure_reason }}</span>
                                            @else
                                                @switch($transaction->type)
                                                    @case('loan_funding')
                                                        @if ($transaction->transactionable)
                                                            To: {{ $transaction->transactionable->borrower->name ?? 'N/A' }}
                                                            (Loan #{{ $transaction->transactionable_id }})
                                                        @endif
                                                    @break

                                                    @case('repayment_received')
                                                        @if ($transaction->transactionable)
                                                            From: {{ $transaction->transactionable->borrower->name ?? 'N/A' }}
                                                            (Loan #{{ $transaction->transactionable_id }})
                                                        @endif
                                                    @break

                                                    @case('repayment')
                                                        @if ($transaction->transactionable)
                                                            To: {{ $transaction->transactionable->lender->name ?? 'N/A' }}
                                                            (Loan #{{ $transaction->transactionable_id }})
                                                        @endif
                                                    @break

                                                    @case('collateral_lock')
                                                    @case('collateral_release')
                                                        Request #{{ $transaction->transactionable_id }}
                                                    @break

                                                    @default
                                                        {{-- Fallback for simple deposits/withdrawals --}}
                                                        {{ $transaction->payhero_transaction_id }}
                                                @endswitch
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            No transactions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $transactions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>