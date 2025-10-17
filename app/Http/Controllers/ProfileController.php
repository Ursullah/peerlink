<?php

namespace App\Http\Controllers;

// Make sure Storage facade is imported
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
        // Fill user data (name, email etc.) from validated request
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle the avatar upload
        if ($request->hasFile('avatar')) {
            Log::info('ProfileController:update - avatar present on request', ['user_id' => $user->id]);

            try {
                // Validate the file (size limit, image type)
                $request->validate([
                    // Increased max size, ensure your php.ini allows large uploads too
                    'avatar' => ['image', 'mimes:jpg,jpeg,png', 'max:10240'], 
                ]);

                // Delete old avatar from S3 if it exists
                if ($user->avatar) {
                    // Use Storage facade with the 's3' disk
                    $deleted = Storage::disk('s3')->delete($user->avatar);
                    Log::info('ProfileController:update - deleted old S3 avatar', [
                        'deleted' => $deleted, 
                        'path' => $user->avatar
                    ]);
                }

                $file = $request->file('avatar');
                Log::info('ProfileController:update - uploading avatar to S3', [
                    'originalName' => $file->getClientOriginalName(), 
                    'size' => $file->getSize()
                ]);

                // Store the new avatar on S3 in the 'avatars' folder, make it public
                $path = $file->storePublicly('avatars', 's3');

                
                // Store only the path, not the full URL, in the database
                $user->avatar = $path; 

                Log::info('ProfileController:update - avatar stored on S3', [
                    'path' => $path, 
                    's3_url' => Storage::disk('s3')->url($path) // Log the public URL for debugging
                ]);

            } catch (\Throwable $ex) {
                Log::error('ProfileController:update - avatar S3 upload failed', [
                    'message' => $ex->getMessage(), 
                    'user_id' => $user->id
                ]);
                // Re-throw or return with error
                 return back()->with('error', 'Avatar upload failed. Please try again.'); 
            }
        } else {
            Log::info('ProfileController:update - no avatar file on request', ['user_id' => $user->id]);
        }

        // Save user model (with potentially updated name, email, avatar path)
        $user->save();

        // Refresh the authenticated user model (might not be necessary depending on session setup)
        // Auth::login($user->fresh()); 

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

        // Perform soft delete (requires SoftDeletes trait on User model)
        $user->delete(); 

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}