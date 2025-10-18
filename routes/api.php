<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayHeroWebhookController; // Import the controller

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public webhook endpoints from PayHero
Route::post('/webhooks/payhero', [PayHeroWebhookController::class, 'handle'])->name('webhooks.payhero');
Route::post('/webhooks/payhero-payout', [PayHeroWebhookController::class, 'handlePayout'])->name('webhooks.payhero.payout');


// Example authenticated API route (you can remove this if not needed)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
