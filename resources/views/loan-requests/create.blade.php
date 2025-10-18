<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Request a New Loan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('loan-requests.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="amount" :value="__('Loan Amount (KES)')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount')" required autofocus />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="repayment_period" :value="__('Repayment Period (in Days)')" />
                            <x-text-input id="repayment_period" class="block mt-1 w-full" type="number" name="repayment_period" :value="old('repayment_period')" required />
                            <x-input-error :messages="$errors->get('repayment_period')" class="mt-2" />
                        </div>

                        

                        <div class="mt-4">
                            <x-input-label for="reason" :value="__('Reason for Loan')" />
                            <textarea id="reason" name="reason" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Submit Request') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>