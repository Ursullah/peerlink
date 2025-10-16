<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $user = $request->user();
    $user->fill($request->validated());

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    // Handle the avatar upload
    if ($request->hasFile('avatar')) {
        Log::info('ProfileController:update - avatar present on request', [
            'user_id' => $user->id,
            'hasFile' => $request->hasFile('avatar'),
        ]);

        try {
            // Validate the file
            $request->validate([
                'avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:10240'], // 10240 KB = 10 MB Max
            ]);

            // Delete old avatar if it exists
            if ($user->avatar) {
                // Delete from the public disk (storage/app/public)
                $deleted = Storage::disk('public')->delete($user->avatar);
                Log::info('ProfileController:update - deleted old avatar', ['deleted' => $deleted, 'path' => $user->avatar]);
            }

            // Store the new avatar and get its path
            $file = $request->file('avatar');
            Log::info('ProfileController:update - uploading avatar', ['originalName' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

            $path = $file->store('avatars', 'public');
            $user->avatar = $path;

            // Verify the file was written to the public disk
            $exists = Storage::disk('public')->exists($path);
            Log::info('ProfileController:update - avatar stored', ['path' => $path, 'exists_on_disk' => $exists]);

            if (! $exists) {
                // If the file isn't present after store(), raise an exception so it's easier to debug
                Log::error('ProfileController:update - avatar not found on disk after store()', ['path' => $path, 'user_id' => $user->id]);
                throw new \RuntimeException('Avatar upload failed: stored file not found on disk.');
            }
        } catch (\Throwable $ex) {
            Log::error('ProfileController:update - avatar upload failed', ['message' => $ex->getMessage(), 'user_id' => $user->id]);
            // rethrow so the usual exception handler captures it (or you can flash an error)
            throw $ex;
        }
    } else {
        Log::info('ProfileController:update - no avatar file on request', ['user_id' => $user->id]);
    }
$user->save();

// Refresh the authenticated user model
Auth::login($user->fresh());

return Redirect::route('profile.edit')
    ->with('status', 'profile-updated')
    ->with('avatar_path', $user->avatar ?? null);

}

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
