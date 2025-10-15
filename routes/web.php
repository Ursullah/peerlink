<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanRequestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $loanRequests = Auth::user()->loanRequests()->latest()->get();
    return view('dashboard', ['loanRequests' => $loanRequests]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/loan-requests/create', [LoanRequestController::class, 'create'])->name('loan-requests.create');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
