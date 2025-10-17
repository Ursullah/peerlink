<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Investments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-semibold mb-4">Loans You Have Funded</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase">
                                        Borrower</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase">
                                        Principal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase">
                                        Repayable</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase">
                                        Interest Earned</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase">Due
                                        Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase">Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($myLoans as $loan)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $loan->borrower->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">KES
                                            {{ number_format($loan->principal_amount / 100, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">KES
                                            {{ number_format($loan->total_repayable / 100, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-green-500 font-semibold">+ KES
                                            {{ number_format($loan->interest_amount / 100, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($loan->due_date)->format('d M, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if ($loan->status == 'active') bg-blue-100 text-blue-800 @endif
                                                @if ($loan->status == 'repaid') bg-green-100 text-green-800 @endif
                                                @if ($loan->status == 'defaulted') bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            You have not funded any loans yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
