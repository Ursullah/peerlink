<?php
use App\Http\Controllers\LoanController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanRequestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // eagerly load the associated loan for funded requests
    $loanRequests = Auth::user()->loanRequests()->with('loan')->latest()->get();
    return view('dashboard', ['loanRequests' => $loanRequests]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/loan-requests/create', [LoanRequestController::class, 'create'])->name('loan-requests.create');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');
    Route::post('/loans/{loan}/repay', [LoanController::class, 'repay'])->name('loans.repay');
    Route::get('/wallet/deposit', [WalletController::class, 'showDepositForm'])->name('wallet.deposit.form');
    Route::post('/wallet/deposit', [WalletController::class, 'processDeposit'])->name('wallet.deposit.process');
    Route::get('/wallet/withdraw', [WalletController::class, 'showWithdrawForm'])->name('wallet.withdraw.form');
    Route::post('/wallet/withdraw', [WalletController::class, 'processWithdraw'])->name('wallet.withdraw.process');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ADMIN ROUTES
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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

// Webhook routes are typically placed here and excluded from CSRF protection.
Route::post('/api/webhooks/payhero', [\App\Http\Controllers\PayHeroWebhookController::class, 'handle'])->name('webhooks.payhero');

require __DIR__.'/auth.php';
