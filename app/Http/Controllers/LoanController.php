<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;
use App\Jobs\InitiatePayHeroPayment;

class LoanController extends Controller
{
    /**
     * Process the repayment of a specific loan.
     */
    public function repay(Loan $loan)
{
    // Security Check: Ensure the person repaying is the borrower and loan is active
    if ($loan->borrower_id !== auth()->id() || $loan->status !== 'active') {
        abort(403, 'Unauthorized or loan is not active for repayment.');
    }

    $borrower = $loan->borrower;
    $amount = $loan->total_repayable / 100; // Convert cents to KES for the API
    $phoneNumber = preg_replace('/^0/', '254', $borrower->phone_number);

    // --- REAL PAYHERO STK PUSH LOGIC ---
    $apiUsername = env('PAYHERO_USERNAME');
    $apiKey = env('PAYHERO_API_KEY');
    $apiEndpoint = 'https://backend.payhero.co.ke/api/v2/payments';

    $channelId = config('app.payhero_channel_id');
    $provider = config('app.payhero_provider', 'm-pesa');

    // Create a pending repayment transaction first
    $transaction = $loan->transactions()->create([
        'user_id' => $borrower->id,
        'type' => 'repayment',
        'amount' => -$loan->total_repayable, // cents
        'status' => 'pending',
    ]);

    // Dispatch job to initiate PayHero payment
    $payload = [
        'phone_number' => $phoneNumber,
        'channel_id' => $channelId,
        'provider' => $provider,
        'callback_url' => url('/api/webhooks/payhero-repayment'),
    ];

    InitiatePayHeroPayment::dispatch($transaction->id, $payload);

    return back()->with('success', 'Repayment initiation queued. You will be notified on completion.');
}
}