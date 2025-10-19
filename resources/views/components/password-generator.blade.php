@props(['targetInputId' => 'password'])

<div x-data="passwordGenerator()" class="space-y-3">
    <div class="flex items-center justify-between">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Generate Secure Password</label>
        <button type="button" @click="generatePassword()"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Generate
        </button>
    </div>

    <!-- Generated Password Display -->
    <div x-show="generatedPassword" x-transition class="space-y-2">
        <div class="flex items-center space-x-2">
            <input type="text" x-model="generatedPassword" readonly
                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono" />
            <button type="button" @click="copyToClipboard()"
                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                :class="copied ? 'bg-green-50 border-green-300 text-green-700' : ''">
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <svg x-show="copied" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span x-text="copied ? 'Copied!' : 'Copy'" class="ml-1"></span>
            </button>
        </div>

        <button type="button" @click="useGeneratedPassword()"
            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Use This Password
        </button>
    </div>

    <!-- Password Options -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="text-xs text-gray-600 dark:text-gray-400">Length: <span x-text="length"></span></label>
            <input type="range" x-model="length" min="8" max="32"
                class="w-20 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer" />
        </div>

        <div class="space-y-1">
            <label class="flex items-center">
                <input type="checkbox" x-model="includeUppercase"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Include Uppercase (A-Z)</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" x-model="includeLowercase"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Include Lowercase (a-z)</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" x-model="includeNumbers"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Include Numbers (0-9)</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" x-model="includeSymbols"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">Include Symbols (!@#$%^&*)</span>
            </label>
        </div>
    </div>
</div>

<script>
    function passwordGenerator() {
        return {
            generatedPassword: '',
            copied: false,
            length: 12,
            includeUppercase: true,
            includeLowercase: true,
            includeNumbers: true,
            includeSymbols: true,

            generatePassword() {
                const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const lowercase = 'abcdefghijklmnopqrstuvwxyz';
                const numbers = '0123456789';
                const symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

                let charset = '';
                if (this.includeUppercase) charset += uppercase;
                if (this.includeLowercase) charset += lowercase;
                if (this.includeNumbers) charset += numbers;
                if (this.includeSymbols) charset += symbols;

                if (charset === '') {
                    alert('Please select at least one character type.');
                    return;
                }

                let password = '';
                for (let i = 0; i < this.length; i++) {
                    password += charset.charAt(Math.floor(Math.random() * charset.length));
                }

                this.generatedPassword = password;
                this.copied = false;
            },

            async copyToClipboard() {
                try {
                    await navigator.clipboard.writeText(this.generatedPassword);
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy: ', err);
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = this.generatedPassword;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 2000);
                }
            },

            useGeneratedPassword() {
                const targetInput = document.getElementById('{{ $targetInputId }}');
                if (targetInput) {
                    targetInput.value = this.generatedPassword;
                    targetInput.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
                this.generatedPassword = '';
            }
        }
    }
</script>
