@php use Illuminate\Support\Facades\Storage; @endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    {{-- ** ADDED A MORE PROMINENT SUCCESS NOTIFICATION HERE ** --}}
    @if (session('status') === 'profile-updated')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
            class="text-sm mt-4 font-medium text-green-600 dark:text-green-400">
            {{ __('Profile updated successfully.') }}
        </p>
    @endif

    {{-- This form is needed for the email verification re-send button to work --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Profile Photo --}}
        <div>
            <x-input-label for="avatar" :value="__('Profile Photo')" />

            {{-- Current avatar image --}}
            <div class="mt-2">
                <img id="current-avatar"
                    src="{{ Auth::user()->avatar ? Storage::disk('s3')->url(Auth::user()->avatar) : asset('images/default-avatar.png') }}"
                    alt="Current Avatar"
                    class="h-20 w-20 rounded-full object-cover border border-gray-300 dark:border-gray-700">
            </div>

            {{-- File input --}}
            <input id="avatar" name="avatar" type="file" accept="image/*"
                class="mt-3 block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100
                          dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600" />

            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Phone Number --}}
        <div class="mt-4">
            <x-input-label for="phone_number" :value="__('Phone Number')" />
            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full"
                :value="old('phone_number', $user->phone_number)" required autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Your phone number for receiving payments and
                notifications.</p>
        </div>

        {{-- National ID (Read-only) --}}
        <div class="mt-4">
            <x-input-label for="national_id_display" :value="__('National ID Number')" />
            <x-text-input id="national_id_display" type="text"
                class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" :value="$user->national_id" disabled
                readonly />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">National ID cannot be changed after registration.
            </p>
        </div>

        {{-- Save button --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>


        </div>
    </form>
</section>

{{-- Preview script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('avatar');
        const currentAvatar = document.getElementById('current-avatar');

        if (!input || !currentAvatar) return;

        input.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file || !file.type.startsWith('image/')) {
                input.value = '';
                return;
            }

            const url = URL.createObjectURL(file);
            currentAvatar.src = url;
        });
    });
</script>
