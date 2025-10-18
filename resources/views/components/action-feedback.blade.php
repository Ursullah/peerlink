@props(['type' => 'loading', 'message' => '', 'show' => false])

<div x-data="{ show: @js($show) }" x-show="show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="show = false">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full mx-4 p-6">
        @if ($type === 'loading')
            <!-- Loading State -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 mb-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Processing...</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $message ?: 'Please wait while we process your request.' }}</p>
                <div class="mt-4">
                    <div class="flex space-x-1 justify-center">
                        <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.1s">
                        </div>
                        <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s">
                        </div>
                    </div>
                </div>
            </div>
        @elseif($type === 'success')
            <!-- Success State -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 mb-4">
                    <div class="rounded-full bg-green-100 dark:bg-green-900 p-3">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Success!</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $message ?: 'Your request has been processed successfully.' }}</p>
                <div class="mt-6">
                    <button @click="show = false"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                        Continue
                    </button>
                </div>
            </div>
        @elseif($type === 'error')
            <!-- Error State -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 mb-4">
                    <div class="rounded-full bg-red-100 dark:bg-red-900 p-3">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Error</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $message ?: 'Something went wrong. Please try again.' }}</p>
                <div class="mt-6 flex space-x-3">
                    <button @click="show = false"
                        class="flex-1 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Close
                    </button>
                    <button @click="show = false; $dispatch('retry-action')"
                        class="flex-1 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                        Retry
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
