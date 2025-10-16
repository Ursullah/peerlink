<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Admin: query PayHero for a transaction status (for debugging/reconciliation)
Route::get('/admin/payhero/transaction/{transactionId}/status', [\App\Http\Controllers\PayHeroAdminController::class, 'status'])->middleware('auth:sanctum');

// Local-only debug route (no auth) for quick polling during development
if (app()->environment('local')) {
    Route::get('/debug/payhero/transaction/{transactionId}/status', [\App\Http\Controllers\PayHeroAdminController::class, 'status']);
}
