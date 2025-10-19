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

            <x-view-container default-view="cards">
                <x-slot name="cards">
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
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">Reputation</span>
                                        <span
                                            class="block text-lg font-bold text-blue-600 dark:text-blue-400">{{ $request->borrower->reputation_score }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-y-4 gap-x-2 text-sm mb-5">
                                    <div>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Amount</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">KES
                                            {{ number_format($request->amount / 100, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Interest
                                            Rate</span>
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

                            <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 relative">
                                <div x-data="{ loading: false }" class="relative">
                                    <div x-show="loading" x-transition
                                        class="absolute inset-0 bg-white dark:bg-gray-900 bg-opacity-90 dark:bg-opacity-90 flex items-center justify-center z-10 rounded">
                                        <div class="text-center">
                                            <div
                                                class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2">
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Funding loan...</p>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('lender.loans.fund', $request) }}"
                                        @submit="loading = true">
                                        @csrf
                                        <div class="flex items-center gap-4">
                                            <div class="relative flex-grow">
                                                <span
                                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">KES</span>
                                                <input type="number" name="amount" step="0.01" min="10"
                                                    max="{{ ($total - $funded) / 100 }}"
                                                    class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                    placeholder="100.00" required>
                                            </div>
                                            <button type="submit" :disabled="loading"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                                :class="{ 'opacity-50 cursor-not-allowed': loading }">
                                                <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                    </path>
                                                </svg>
                                                <span x-text="loading ? 'Funding...' : 'Fund'"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center text-gray-500 dark:text-gray-400">
                            No active loan requests available for funding.
                        </div>
                    @endforelse
                </x-slot>

                <x-slot name="listHeaders">
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Borrower</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Amount</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Interest</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Period</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Progress</th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Action</th>
                </x-slot>

                <x-slot name="listRows">
                    @forelse ($loanRequests as $request)
                        @php
                            $funded = $request->loans->sum('principal_amount');
                            $total = $request->amount;
                            $progressPercent = $total > 0 ? ($funded / $total) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div
                                            class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                                {{ substr($request->borrower->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $request->borrower->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Score:
                                            {{ $request->borrower->reputation_score }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                KES {{ number_format($request->amount / 100, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $request->interest_rate }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $request->repayment_period }} days
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                        <div class="bg-green-600 h-2 rounded-full"
                                            style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <span
                                        class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ number_format($progressPercent, 0) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" action="{{ route('lender.loans.fund', $request) }}"
                                    class="inline">
                                    @csrf
                                    <div class="flex items-center space-x-2">
                                        <input type="number" name="amount" step="0.01" min="10"
                                            max="{{ ($total - $funded) / 100 }}"
                                            class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-300"
                                            placeholder="100" required>
                                        <button type="submit"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Fund
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No active loan requests available for funding.
                            </td>
                        </tr>
                    @endforelse
                </x-slot>

                <x-slot name="grid">
                    @forelse ($loanRequests as $request)
                        @php
                            $funded = $request->loans->sum('principal_amount');
                            $total = $request->amount;
                            $progressPercent = $total > 0 ? ($funded / $total) * 100 : 0;
                        @endphp
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-center">
                                <div
                                    class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mx-auto mb-3">
                                    <span class="text-lg font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ substr($request->borrower->name, 0, 1) }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                                    {{ $request->borrower->name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Score:
                                    {{ $request->borrower->reputation_score }}</p>

                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Amount:</span>
                                        <span class="font-medium">KES
                                            {{ number_format($request->amount / 100, 0) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Interest:</span>
                                        <span class="font-medium">{{ $request->interest_rate }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Period:</span>
                                        <span class="font-medium">{{ $request->repayment_period }}d</span>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-green-600 dark:text-green-400">Progress</span>
                                        <span
                                            class="text-gray-600 dark:text-gray-400">{{ number_format($progressPercent, 0) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="bg-green-600 h-1.5 rounded-full"
                                            style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('lender.loans.fund', $request) }}"
                                    class="mt-4">
                                    @csrf
                                    <div class="space-y-2">
                                        <input type="number" name="amount" step="0.01" min="10"
                                            max="{{ ($total - $funded) / 100 }}"
                                            class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-300"
                                            placeholder="100" required>
                                        <button type="submit"
                                            class="w-full bg-indigo-600 text-white text-xs py-1 px-3 rounded hover:bg-indigo-700 transition-colors">
                                            Fund
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center text-gray-500 dark:text-gray-400">
                            No active loan requests available for funding.
                        </div>
                    @endforelse
                </x-slot>
            </x-view-container>
        </div>
    </div>
</x-app-layout>
