@props(['loan', 'maxAmount'])

<div x-data="partialRepayment()" class="space-y-4">
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Partial Repayment Available</h4>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    You can repay any amount up to KES {{ number_format($maxAmount / 100, 2) }}
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('loans.partial-repay', $loan) }}" @submit="submitForm">
        @csrf
        <div>
            <x-input-label for="amount" :value="__('Repayment Amount (KES)')" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">KES</span>
                </div>
                <input id="amount" name="amount" type="number" step="0.01" min="10"
                    :max="{{ $maxAmount / 100 }}" x-model="amount" required autofocus
                    class="pl-12 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    placeholder="100.00" />
            </div>
            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Minimum: KES 10 | Maximum: KES {{ number_format($maxAmount / 100, 2) }}
            </p>
        </div>

        <!-- Repayment Preview -->
        <div x-show="amount > 0" x-transition class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Repayment Preview</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Amount to repay:</span>
                    <span class="font-medium" x-text="'KES ' + (amount || '0.00')"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Remaining balance:</span>
                    <span class="font-medium"
                        x-text="'KES ' + ({{ $maxAmount / 100 }} - (amount || 0)).toFixed(2)"></span>
                </div>
                <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-2">
                    <span class="text-gray-900 dark:text-gray-100 font-medium">Interest portion:</span>
                    <span class="font-medium text-green-600 dark:text-green-400"
                        x-text="'KES ' + calculateInterest(amount).toFixed(2)"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <button type="submit" :disabled="loading || amount < 10 || amount > {{ $maxAmount / 100 }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                :class="{ 'opacity-50 cursor-not-allowed': loading || amount < 10 || amount > {{ $maxAmount / 100 }} }">
                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                    </path>
                </svg>
                <span x-text="loading ? 'Processing...' : 'Make Partial Repayment'"></span>
            </button>
        </div>
    </form>
</div>

<script>
    function partialRepayment() {
        return {
            amount: '',
            loading: false,
            interestRate: {{ $loan->loanRequest->interest_rate ?? 12.5 }},

            calculateInterest(amount) {
                if (!amount || amount <= 0) return 0;
                const principalAmount = {{ $loan->principal_amount / 100 }};
                const totalRepayable = {{ $loan->total_repayable / 100 }};
                const interestAmount = totalRepayable - principalAmount;
                const interestRatio = amount / totalRepayable;
                return interestAmount * interestRatio;
            },

            submitForm(event) {
                if (this.amount < 10) {
                    alert('Minimum repayment amount is KES 10');
                    return;
                }

                if (this.amount > {{ $maxAmount / 100 }}) {
                    alert('Repayment amount cannot exceed the total loan amount');
                    return;
                }

                this.loading = true;
            }
        }
    }
</script>
