<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        'phone_number' => ['required', 'string', 'max:255', 'unique:'.User::class],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class], // Add this validation
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'phone_number' => $request->phone_number,
        'email' => $request->email, // Add this to save the email
        'password' => Hash::make($request->password),
    ]);
    
    // Create a wallet for the new user
    $user->wallet()->create(['balance' => 0]);

    event(new Registered($user));

    Auth::login($user);

    // Redirect based on role
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'lender') {
        return redirect()->route('lender.loans.index');
    }
    return redirect(route('dashboard', absolute: false));
}
}
