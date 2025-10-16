<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin: Pending Loan Requests') }}
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-semibold mb-4">Requests Awaiting Approval</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Borrower</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase">Reason</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($loanRequests as $request)
                                    <tr>
                                        <td class="px-6 py-4">{{ $request->borrower->name }}</td>
                                        <td class="px-6 py-4">KES {{ number_format($request->amount / 100, 2) }}</td>
                                        <td class="px-6 py-4">{{ $request->repayment_period }} days</td>
                                        <td class="px-6 py-4 text-sm max-w-xs truncate">{{ $request->reason }}</td>
                                        <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <form method="POST" action="{{ route('admin.loans.approve', $request) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700 transition">
                                                Approve
                                            </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.loans.reject', $request) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700 transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">No pending loan requests.</td>
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