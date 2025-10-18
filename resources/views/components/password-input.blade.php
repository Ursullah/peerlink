@props(['name', 'label', 'required' => false, 'autocomplete' => 'new-password'])

<div x-data="passwordValidation()" class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <div class="relative">
        <input id="{{ $name }}" name="{{ $name }}" type="password" x-model="password"
            @input="validatePassword()" @focus="showValidation = true" @blur="showValidation = false"
            :class="[
                'block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600',
                passwordError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300'
            ]"
            autocomplete="{{ $autocomplete }}" {{ $required ? 'required' : '' }} />

        <!-- Password Toggle Button -->
        <button type="button" @click="togglePasswordVisibility()"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
            </svg>
        </button>
    </div>

    <!-- Password Strength Indicator -->
    <div x-show="showValidation && password.length > 0" x-transition class="space-y-2">
        <div class="flex items-center space-x-2">
            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="h-2 rounded-full transition-all duration-300" :class="strengthColor"
                    :style="`width: ${strengthPercentage}%`"></div>
            </div>
            <span class="text-xs font-medium" :class="strengthTextColor" x-text="strengthText"></span>
        </div>

        <!-- Validation Rules -->
        <div class="space-y-1 text-xs">
            <div class="flex items-center space-x-2"
                :class="rules.length ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                <svg class="h-3 w-3" :class="rules.length ? 'text-green-500' : 'text-gray-400'" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span>At least 8 characters</span>
            </div>
            <div class="flex items-center space-x-2"
                :class="rules.uppercase ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                <svg class="h-3 w-3" :class="rules.uppercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span>One uppercase letter</span>
            </div>
            <div class="flex items-center space-x-2"
                :class="rules.lowercase ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                <svg class="h-3 w-3" :class="rules.lowercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span>One lowercase letter</span>
            </div>
            <div class="flex items-center space-x-2"
                :class="rules.number ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                <svg class="h-3 w-3" :class="rules.number ? 'text-green-500' : 'text-gray-400'" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span>One number</span>
            </div>
            <div class="flex items-center space-x-2"
                :class="rules.special ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                <svg class="h-3 w-3" :class="rules.special ? 'text-green-500' : 'text-gray-400'" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
                <span>One special character</span>
            </div>
        </div>
    </div>

    <!-- Error Message -->
    <div x-show="passwordError" x-text="passwordError" class="text-sm text-red-600 dark:text-red-400"></div>
</div>

<script>
    function passwordValidation() {
        return {
            password: '',
            showPassword: false,
            showValidation: false,
            passwordError: '',
            strengthPercentage: 0,
            strengthText: '',
            strengthColor: 'bg-gray-300',
            strengthTextColor: 'text-gray-500',
            rules: {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            },

            togglePasswordVisibility() {
                this.showPassword = !this.showPassword;
                const input = document.getElementById('{{ $name }}');
                input.type = this.showPassword ? 'text' : 'password';
            },

            validatePassword() {
                const password = this.password;

                // Reset rules
                this.rules = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /\d/.test(password),
                    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
                };

                // Calculate strength
                const validRules = Object.values(this.rules).filter(Boolean).length;
                this.strengthPercentage = (validRules / 5) * 100;

                // Set strength text and color
                if (this.strengthPercentage < 20) {
                    this.strengthText = 'Very Weak';
                    this.strengthColor = 'bg-red-500';
                    this.strengthTextColor = 'text-red-500';
                } else if (this.strengthPercentage < 40) {
                    this.strengthText = 'Weak';
                    this.strengthColor = 'bg-orange-500';
                    this.strengthTextColor = 'text-orange-500';
                } else if (this.strengthPercentage < 60) {
                    this.strengthText = 'Fair';
                    this.strengthColor = 'bg-yellow-500';
                    this.strengthTextColor = 'text-yellow-500';
                } else if (this.strengthPercentage < 80) {
                    this.strengthText = 'Good';
                    this.strengthColor = 'bg-blue-500';
                    this.strengthTextColor = 'text-blue-500';
                } else {
                    this.strengthText = 'Strong';
                    this.strengthColor = 'bg-green-500';
                    this.strengthTextColor = 'text-green-500';
                }

                // Set error if password is too weak
                if (password.length > 0 && validRules < 3) {
                    this.passwordError = 'Password is too weak. Please meet more requirements.';
                } else {
                    this.passwordError = '';
                }
            }
        }
    }
</script>
