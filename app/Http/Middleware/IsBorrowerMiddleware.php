<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // 1. Import the Auth facade

class IsBorrowerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 2. Check if the user is a borrower. If so, let them pass.
        if (Auth::check() && Auth::user()->role === 'borrower') {
            return $next($request);
        }

        // 3. If they are NOT a borrower, but are logged in, redirect them.
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            if ($user->role === 'lender') {
                return redirect()->route('lender.dashboard');
            }
        }
        
        // 4. If they are not a borrower and not logged in (or have an unknown role),
        // send them to the login page.
        return redirect()->route('login');
    }
}
