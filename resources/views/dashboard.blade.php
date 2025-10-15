<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="p-6 text-gray-900 dark:text-gray-100">
    <h2 class="text-2xl font-semibold">Welcome, {{ Auth::user()->name }}!</h2>
    <p class="mt-2">Ready to get started? Request a new loan to connect with lenders.</p>

    <div class="mt-6">
        <x-primary-button :href="route('loan-requests.create')">
            Request a New Loan
        </x-primary-button>
    </div>
</div>
</x-app-layout>
