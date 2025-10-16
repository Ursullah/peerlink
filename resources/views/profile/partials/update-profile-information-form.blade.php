<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

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
     src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) . '?v=' . time() : asset('images/default-avatar.png') }}"
     alt="Current Avatar"
     class="h-20 w-20 rounded-full object-cover border border-gray-300 dark:border-gray-700">

            </div>

            {{-- File input --}}
            <input id="avatar"
                   name="avatar"
                   type="file"
                   accept="image/*"
                   class="mt-3 block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200"/>

            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

            @if (session('avatar_path'))
                <p class="mt-2 text-sm text-green-600 dark:text-green-400">
                    {{ __('Avatar updated successfully!') }}
                </p>
            @endif
        </div>

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name"
                          name="name"
                          type="text"
                          class="mt-1 block w-full"
                          :value="old('name', $user->name)"
                          required
                          autofocus
                          autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
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

{{-- Preview script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('avatar');
    const currentAvatar = document.getElementById('current-avatar');

    if (!input || !currentAvatar) return;

    input.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const url = URL.createObjectURL(file);
        currentAvatar.src = url;
    });
});
if (window.location.search.includes('status=profile-updated')) {
    setTimeout(() => window.location.reload(), 500);
}

</script>
