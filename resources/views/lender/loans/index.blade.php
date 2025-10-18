<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lender: Browse Active Loan Requests') }}
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

            {{-- --- NEW: VALIDATION ERROR BLOCK --- --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                    role="alert">
                    <strong class="font-bold">Oops! Something went wrong.</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{-- --- END OF NEW BLOCK --- --}}

            <h3 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-gray-100">
                Available for Funding ({{ $loanRequests->count() }})
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                @forelse ($loanRequests as $request)
                    @php
                        $funded = $request->loans->sum('principal_amount');
                        $total = $request->amount;
                        $progressPercent = $total > 0 ? ($funded / $total) * 100 : 0;
                    @endphp

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700 flex flex-col">
                        <div class="p-6 flex-grow">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $request->borrower->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $request->reason ?? 'Loan Request' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reputation</span>
                                    <span
                                        class="block text-lg font-bold text-blue-600 dark:text-blue-400">{{ $request->borrower->reputation_score }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-y-4 gap-x-2 text-sm mb-5">
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Total Amount</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200">KES
                                        {{ number_format($request->amount / 100, 2) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Interest Rate</span>
                                    <span
                                        class="font-medium text-gray-800 dark:text-gray-200">{{ $request->interest_rate }}%</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Period</span>
                                    <span
                                        class="font-medium text-gray-800 dark:text-gray-200">{{ $request->repayment_period }}
                                        days</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-green-700 dark:text-green-400">Funding
                                        Progress</span>
                                    <span
                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ number_format($progressPercent, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-green-600 h-2.5 rounded-full"
                                        style="width: {{ $progressPercent }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-right">
                                    KES {{ number_format($funded / 100, 2) }} / KES
                                    {{ number_format($total / 100, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4">
                            <form method="POST" action="{{ route('lender.loans.fund', $request) }}">
                                @csrf
                                <div class="flex items-center gap-4">
                                    <div class="relative flex-grow">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">KES</span>
                                        <input type="number" name="amount" step="0.01"
                                            class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            placeholder="100.00">
                                    </div>
                                    <x-primary-button>Fund</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div
                        class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center text-gray-500 dark:text-gray-400">
                        No active loan requests available for funding.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
