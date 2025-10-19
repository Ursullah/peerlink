<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Lender\DashboardController as LenderDashboardController;
use App\Http\Controllers\Lender\LoanController as LenderLoanController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// BORROWER ROUTES
Route::middleware(['auth', 'borrower'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        // --- FIX: Changed with('loan') to with('loans') to match the new relationship ---
        $loanRequests = $user->loanRequests()->with('loans')->latest()->get();

        $stats = [
            'active_loan_count' => \App\Models\Loan::where('borrower_id', $user->id)->where('status', 'active')->count(),
            'total_borrowed' => \App\Models\Loan::where('borrower_id', $user->id)->sum('principal_amount'),
            'total_repaid' => \App\Models\Loan::where('borrower_id', $user->id)->sum('amount_repaid'),
            'reputation_score' => $user->reputation_score,
        ];
        $recentTransactions = $user->transactions()->latest()->take(5)->get();

        return view('dashboard', compact('loanRequests', 'stats', 'recentTransactions'));
    })->name('dashboard');

    Route::get('/loan-requests/create', [LoanRequestController::class, 'create'])->name('loan-requests.create');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');

    // ---  repay routes  ---
    Route::post('/loan-requests/{loanRequest}/repay', [LoanRequestController::class, 'repay'])->name('loan-requests.repay');
    Route::post('/loans/{loan}/partial-repay', [\App\Http\Controllers\LoanController::class, 'partialRepay'])->name('loans.partial-repay');
});

// ADMIN ROUTES
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans.index');
    Route::patch('/loans/{loanRequest}/approve', [AdminLoanController::class, 'approve'])->name('loans.approve');
    Route::patch('/loans/{loanRequest}/reject', [AdminLoanController::class, 'reject'])->name('loans.reject');
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue.index');
});

// LENDER ROUTES
Route::middleware(['auth', 'lender'])->prefix('lender')->name('lender.')->group(function () {
    Route::get('/dashboard', [LenderDashboardController::class, 'index'])->name('dashboard');

    // --- UPDATE: Standardized lender loan routes ---
    Route::get('/loans', [LenderLoanController::class, 'index'])->name('loans.index'); // Browse loans
    Route::post('/loans/{loanRequest}/fund', [LenderLoanController::class, 'fund'])->name('loans.fund'); // Fund a loan
    Route::get('/loans/investments', [LenderLoanController::class, 'investments'])->name('loans.investments'); // My investments
});

// WALLET ROUTES (for all authenticated users except admins)
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet/deposit', [WalletController::class, 'showDepositForm'])->name('wallet.deposit.form');
    Route::post('/wallet/deposit', [WalletController::class, 'processDeposit'])->name('wallet.deposit.process');
    Route::get('/wallet/withdraw', [WalletController::class, 'showWithdrawForm'])->name('wallet.withdraw.form');
    Route::post('/wallet/withdraw', [WalletController::class, 'processWithdraw'])->name('wallet.withdraw.process');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
});

require __DIR__.'/auth.php';
