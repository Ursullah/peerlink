<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WalletController extends Controller
{
    /**
     * Show the form for depositing funds.
     */
    public function showDepositForm()
    {
        return view('wallet.deposit');
    }

    /**
     * Process the deposit request by initiating a PayHero STK Push.
     */
    public function processDeposit(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:10',
    ]);

    $user = Auth::user();
    // Convert the amount from a string to a number (float)
    $amount = (float) $validated['amount']; // <-- THIS IS THE FIX

    $phoneNumber = preg_replace('/^0/', '254', $user->phone_number); 

    $apiUsername = env('PAYHERO_USERNAME');
    $apiKey = env('PAYHERO_API_KEY');
    $apiEndpoint = 'https://backend.payhero.co.ke/api/v2/payments';

    $payload = [
        'amount' => $amount,
        'phone_number' => $phoneNumber,
        'channel_id' => 911,
        'provider' => 'm-pesa',
        'external_reference' => 'PEERLINK_TXN_'.uniqid(),
        'callback_url' => url('/api/webhooks/payhero'),
    ];
    
    $response = Http::withBasicAuth($apiUsername, $apiKey)
                    ->acceptJson()
                    ->post($apiEndpoint, $payload);

    if ($response->successful()) {
        $responseData = $response->json();

        $user->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount * 100,
            'status' => 'pending',
            'payhero_transaction_id' => $responseData['id'] ?? $payload['external_reference'],
        ]);

        return redirect()->route('dashboard')->with('success', 'STK Push initiated successfully. Please check your phone and enter your M-Pesa PIN.');
    } else {
        Log::error('PayHero API Error:', ['response' => $response->body()]);
        return back()->with('error', 'Payment could not be initiated. The provider returned an error.');
    }
}


    public function showWithdrawForm()
    {
        return view('wallet.withdraw');
    }

    /**
     * Process the withdrawal request.
     */
    public function processWithdraw(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:50|max:' . ($wallet->balance / 100),
        ]);

        $amountInKES = (float) $validated['amount'];
        $amountInCents = $amountInKES * 100;
        
        // --- SIMULATED PAYHERO PAYOUT API CALL ---
        // In a real application, you would call PayHero's Payouts API here.
        // We will simulate a successful API call.
        
        DB::transaction(function () use ($user, $wallet, $amountInCents) {
            // 1. Debit the user's wallet
            $wallet->balance -= $amountInCents;
            $wallet->save();

            // 2. Create a 'withdrawal' transaction record
            $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amountInCents, // Use a negative value for debits
                'status' => 'successful', // Assume payout is instant for now
                'payhero_transaction_id' => 'PO_'.uniqid(),
            ]);
        });

        return redirect()->route('dashboard')->with('success', "Your withdrawal of KES {$amountInKES} has been processed successfully.");
    }
}