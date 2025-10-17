<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lender: Browse Active Loan Requests') }}
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-semibold mb-4">Available for Funding</h3>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase dark:text-gray-400">Borrower</th> 
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Interest Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Borrower Reputation</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($loanRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $request->borrower->name }}</td>
                                    <td class="px-6 py-4">KES {{ number_format($request->amount / 100, 2) }}</td>
                                    <td class="px-6 py-4">{{ $request->interest_rate }}%</td>
                                    <td class="px-6 py-4">{{ $request->repayment_period }} days</td>
                                    <td class="px-6 py-4">{{ $request->borrower->reputation_score }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <form method="POST" action="{{ route('lender.loans.fund', $request) }}">
                                            @csrf
                                            <x-primary-button>Fund Loan</x-primary-button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center">No active loan requests available for funding.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>