<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
        // Validate the file
        $request->validate([
            'avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB Max
        ]);

        // Delete old avatar if it exists
        if ($user->avatar) {
            Storage::delete('public/' . $user->avatar);
        }

        // Store the new avatar and get its path
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
    }

    $user->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
