@props(['show' => false, 'message' => 'Processing...'])

<div x-data="{ show: @js($show) }" x-show="show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-90 dark:bg-opacity-90 flex items-center justify-center z-10 rounded-lg">
    <div class="text-center">
        <!-- Spinning Loader -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 mb-4">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-gray-300 border-t-indigo-600"></div>
        </div>

        <!-- Loading Message -->
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $message }}</h3>

        <!-- Animated Dots -->
        <div class="flex space-x-1 justify-center">
            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce"></div>
            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
            <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        </div>

        <!-- Progress Bar (Optional) -->
        <div class="mt-4 w-48 mx-auto">
            <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full animate-pulse" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>
