<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Withdraw Funds From Your Wallet') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg relative">
                <!-- Loading Overlay -->
                <x-loading-overlay :show="false" message="Processing withdrawal..." />

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Balance Display -->
                    <div
                        class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400">Available Balance</p>
                                <p class="text-lg font-bold text-blue-800 dark:text-blue-200">KES
                                    {{ number_format(Auth::user()->wallet->balance / 100, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    @if (session('error'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <strong class="font-bold">Error!</strong>
                                <span class="block sm:inline ml-2">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <strong class="font-bold">Success!</strong>
                                <span class="block sm:inline ml-2">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('wallet.withdraw.process') }}" x-data="withdrawForm()"
                        @submit="submitForm">
                        @csrf
                        <div>
                            <x-input-label for="amount" :value="__('Amount to Withdraw (KES)')" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">KES</span>
                                </div>
                                <input id="amount" name="amount" type="number" step="0.01" min="50"
                                    :max="{{ Auth::user()->wallet->balance / 100 }}" x-model="amount" required autofocus
                                    class="pl-12 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="100.00" />
                            </div>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum withdrawal: KES 50</p>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit"
                                :disabled="loading || amount < 50 || amount > {{ Auth::user()->wallet->balance / 100 }}"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :class="{ 'opacity-50 cursor-not-allowed': loading || amount < 50 || amount >
                                        {{ Auth::user()->wallet->balance / 100 }} }">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                                <span x-text="loading ? 'Processing...' : 'Request Withdrawal'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Feedback Modal -->
    <x-action-feedback type="loading" message="Processing withdrawal request..." :show="false" />
    <x-action-feedback type="success"
        message="Withdrawal request submitted successfully! You will receive the funds shortly." :show="false" />
    <x-action-feedback type="error" message="Withdrawal request failed. Please try again." :show="false" />

    <script>
        function withdrawForm() {
            return {
                amount: '',
                loading: false,
                maxAmount: {{ Auth::user()->wallet->balance / 100 }},

                submitForm(event) {
                    if (this.amount < 50) {
                        alert('Minimum withdrawal amount is KES 50');
                        return;
                    }

                    if (this.amount > this.maxAmount) {
                        alert('Insufficient balance');
                        return;
                    }

                    this.loading = true;

                    // Show loading overlay
                    const overlay = document.querySelector('[x-data*="loading-overlay"]');
                    if (overlay) {
                        overlay.setAttribute('x-data', '{ show: true }');
                    }

                    // Simulate processing time (remove in production)
                    setTimeout(() => {
                        this.loading = false;
                    }, 2000);
                }
            }
        }
    </script>
</x-app-layout>
