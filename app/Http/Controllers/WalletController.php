<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\InitiatePayHeroPayment;

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

    // Read channel and provider from config so they can be changed without editing code
    $channelId = config('app.payhero_channel_id');
    $provider = config('app.payhero_provider', 'm-pesa');

    // 1) Create a local pending transaction with external reference
    $transaction = $user->transactions()->create([
        'type' => 'deposit',
        'amount' => (int) ($amount * 100),
        'status' => 'pending',
    ]);

    // 2) Dispatch a job to initiate the PayHero payment asynchronously
    $payload = [
        'phone_number' => $phoneNumber,
        'channel_id' => $channelId,
        'provider' => $provider,
        'callback_url' => url('/api/webhooks/payhero'),
    ];

    InitiatePayHeroPayment::dispatch($transaction->id, $payload);

    return redirect()->route('dashboard')->with('success', 'Payment initiation queued. You will be notified on completion.');
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
        
        // Create a withdrawal transaction, debit the wallet, and dispatch payout job
        DB::transaction(function () use ($user, $wallet, $amountInCents, $amountInKES) {
            // 1. Debit the user's wallet
            $wallet->balance -= $amountInCents;
            $wallet->save();

            // 2. Create a 'withdrawal' transaction record (pending)
            $transaction = $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amountInCents, // Use a negative value for debits
                'status' => 'pending',
            ]);

            // 3. Dispatch payout job with destination details (e.g., phone or bank account)
            $payload = [
                'amount' => $amountInKES,
                'destination' => [
                    // Fill in the destination details per PayHero's payouts API spec.
                    'phone_number' => preg_replace('/^0/', '254', $user->phone_number),
                ],
                'external_reference' => 'WD_' . $transaction->id . '_' . now()->timestamp,
                'metadata' => ['user_id' => $user->id, 'type' => 'withdrawal'],
            ];

            InitiatePayHeroPayout::dispatch($transaction->id, $payload);
        });

        return redirect()->route('dashboard')->with('success', "Your withdrawal of KES {$amountInKES} has been queued and will be processed shortly.");
    }
}