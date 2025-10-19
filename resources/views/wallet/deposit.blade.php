<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Top-Up Your Wallet') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg relative">
                <!-- Loading Overlay -->
                <x-loading-overlay :show="false" message="Initiating payment..." />

                <div class="p-6 text-gray-900 dark:text-gray-100">
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

                    <form method="POST" action="{{ route('wallet.deposit.process') }}" x-data="depositForm()"
                        @submit="submitForm">
                        @csrf
                        <div>
                            <x-input-label for="amount" :value="__('Amount to Deposit (KES)')" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">KES</span>
                                </div>
                                <input id="amount" name="amount" type="number" step="0.01" min="10"
                                    x-model="amount" required autofocus
                                    class="pl-12 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="100.00" />
                            </div>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" :disabled="loading"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                :class="{ 'opacity-50 cursor-not-allowed': loading }">
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
                                <span x-text="loading ? 'Processing...' : 'Proceed to Payment'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Feedback Modal -->
    <x-action-feedback type="loading" message="Initiating payment with PayHero..." :show="false" />
    <x-action-feedback type="success"
        message="Payment initiated successfully! Please check your phone for the STK Push." :show="false" />
    <x-action-feedback type="error" message="Payment initiation failed. Please try again." :show="false" />

    <script>
        function depositForm() {
            return {
                amount: '',
                loading: false,

                submitForm(event) {
                    if (this.amount < 10) {
                        alert('Minimum deposit amount is KES 10');
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
