@php use Illuminate\Support\Facades\Storage; @endphp {{-- Add this line at the top --}}

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information.") }} {{-- Removed email address part for clarity --}}
        </p>
    </header>
    @if (session('status') === 'profile-updated')
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2000)"
            class="text-sm mt-2 font-medium text-green-600 dark:text-green-400"
        >
            {{ __('Profile updated successfully.') }}
        </p>
    @endif

    {{-- Removed verification form as it's handled separately --}}
    {{-- <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form> --}}

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Profile Photo --}}
        <div>
            <x-input-label for="avatar" :value="__('Profile Photo')" />

            {{-- Current avatar image --}}
            <div class="mt-2">
                <img id="current-avatar"
                     {{-- Use Storage facade to get S3 URL --}}
                     src="{{ Auth::user()->avatar ? Storage::disk('s3')->url(Auth::user()->avatar) : asset('images/default-avatar.png') }}"
                     alt="Current Avatar"
                     class="h-20 w-20 rounded-full object-cover border border-gray-300 dark:border-gray-700">
            </div>

            {{-- File input --}}
            <input id="avatar"
                   name="avatar"
                   type="file"
                   accept="image/*" {{-- Only allow image files --}}
                   class="mt-3 block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100
                          dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600"/> {{-- Added dark mode styles --}}

            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

            {{-- Removed the old avatar_path check --}}
        </div>

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Email - This should ideally be in a separate form section as updating it might require re-verification --}}
        {{-- For simplicity, keeping it here for now --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
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


        {{-- Save button --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>

{{-- Preview script - This script remains the same --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('avatar');
    const currentAvatar = document.getElementById('current-avatar');

    if (!input || !currentAvatar) return;

    input.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file || !file.type.startsWith('image/')){ // Check if it's an image
             // Optionally reset or show an error if not an image
             input.value = ''; // Clear the input
             // Maybe set src back to original? Depends on UX preference.
             // currentAvatar.src = "{{ Auth::user()->avatar ? Storage::disk('s3')->url(Auth::user()->avatar) : asset('images/default-avatar.png') }}";
             return; 
        }

        const url = URL.createObjectURL(file);
        currentAvatar.src = url;
        // Optional: Revoke the object URL later to free up memory
        // currentAvatar.onload = () => { URL.revokeObjectURL(url); } 
    });
});

// Remove the reload script, the Alpine 'Saved.' message handles feedback
// if (window.location.search.includes('status=profile-updated')) {
//    setTimeout(() => window.location.reload(), 500);
// }
</script>