<?php

use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <-- 1. ADDED THIS IMPORT

Route::get('/', function () {
    return view('welcome');
});

// BORROWER ROUTES
Route::middleware(['auth', 'verified', 'borrower'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $loanRequests = $user->loanRequests()->with('loan')->latest()->get();
        $stats = [
            'active_loan_count' => \App\Models\Loan::where('borrower_id', $user->id)->where('status', 'active')->count(),
            'total_borrowed' => \App\Models\Loan::where('borrower_id', $user->id)->where('status', '!=', 'defaulted')->sum('principal_amount'),
            'reputation_score' => $user->reputation_score,
        ];
        $recentTransactions = $user->transactions()->latest()->take(5)->get();
        return view('dashboard', compact('loanRequests', 'stats', 'recentTransactions'));
    })->name('dashboard'); // <-- 2. REMOVED DUPLICATE MIDDLEWARE

    Route::get('/loan-requests/create', [LoanRequestController::class, 'create'])->name('loan-requests.create');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');
    Route::post('/loans/{loan}/repay', [LoanController::class, 'repay'])->name('loans.repay');
});

// WALLET ROUTES (Accessible by Borrowers and Lenders)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/wallet/deposit', [WalletController::class, 'showDepositForm'])->name('wallet.deposit.form');
    Route::post('/wallet/deposit', [WalletController::class, 'processDeposit'])->name('wallet.deposit.process');
    Route::get('/wallet/withdraw', [WalletController::class, 'showWithdrawForm'])->name('wallet.withdraw.form');
    Route::post('/wallet/withdraw', [WalletController::class, 'processWithdraw'])->name('wallet.withdraw.process');
});

// PROFILE ROUTES (Accessible by all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ADMIN ROUTES
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/loans', [App\Http\Controllers\Admin\LoanController::class, 'index'])->name('loans.index');
    Route::patch('/loans/{loanRequest}/approve', [App\Http\Controllers\Admin\LoanController::class, 'approve'])->name('loans.approve');
    Route::patch('/loans/{loanRequest}/reject', [App\Http\Controllers\Admin\LoanController::class, 'reject'])->name('loans.reject');
});

// LENDER ROUTES
Route::middleware(['auth', 'lender'])->prefix('lender')->name('lender.')->group(function () {
    Route::get('/loans', [App\Http\Controllers\Lender\LoanController::class, 'index'])->name('loans.index');
    Route::post('/loans/{loanRequest}/fund', [App\Http\Controllers\Lender\LoanController::class, 'fund'])->name('loans.fund');
    Route::get('/my-investments', [App\Http\Controllers\Lender\LoanController::class, 'investments'])->name('loans.investments');
});

// 3. REMOVED WEBHOOK ROUTE FROM HERE

require __DIR__.'/auth.php';

