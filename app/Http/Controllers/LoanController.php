<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;

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

    $payload = [
        'amount' => $amount,
        'phone_number' => $phoneNumber,
        'channel_id' => 911,
        'provider' => 'm-pesa',
        'external_reference' => 'REPAY_'.uniqid(),
        // Good practice to use a dedicated webhook for repayments
        'callback_url' => url('/api/webhooks/payhero-repayment'), 
    ];

    $response = Http::withBasicAuth($apiUsername, $apiKey)->acceptJson()->post($apiEndpoint, $payload);

    if ($response->successful()) {
        // Create a pending transaction. The actual loan update will happen in the webhook.
        $loan->transactions()->create([
            'user_id' => $borrower->id,
            'type' => 'repayment',
            'amount' => -$loan->total_repayable, // Store in cents
            'status' => 'pending',
            'payhero_transaction_id' => $response->json()['id'] ?? $payload['external_reference'],
        ]);

        return back()->with('success', 'Repayment initiated. Please check your phone and enter your M-Pesa PIN.');
    } else {
        Log::error('PayHero Repayment Error:', ['response' => $response->body()]);
        return back()->with('error', 'Repayment could not be initiated. The provider returned an error.');
    }
}
}