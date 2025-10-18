<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255', Rule::unique('users', 'phone_number')->whereNull('deleted_at')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            // Validate National ID (uncommented and required)
            'national_id' => ['required', 'string', 'max:20', Rule::unique('users', 'national_id')->whereNull('deleted_at')],
            // Validate Role
            'role' => ['required', Rule::in(['borrower', 'lender'])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'national_id' => $request->national_id, // Save National ID
            'role' => $request->role,             // Save Role
            'password' => Hash::make($request->password),
        ]);

        $user->wallet()->create(['balance' => 0]);

        event(new Registered($user));

        Auth::login($user);

        // This direct redirect logic is correct
        $role = strtolower($user->role);
        if ($role === 'admin') {
            return redirect()->to('/admin/dashboard');
        } elseif ($role === 'lender') {
            return redirect()->to('/lender/loans');
        } elseif ($role === 'borrower') {
            return redirect()->to('/dashboard');
        }

        // Fallback in case of an unknown role
        Auth::logout();
        return redirect('/')->withErrors(['role' => 'Your account role is not recognized.']);
    }
}
