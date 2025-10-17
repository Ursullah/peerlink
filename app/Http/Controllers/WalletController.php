<?php

namespace App\Http\Controllers;

use App\Jobs\InitiatePayHeroPayment;
use App\Jobs\InitiatePayHeroPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function showDepositForm()
    {
        return view('wallet.deposit');
    }

    public function processDeposit(Request $request)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:10']);
        $user = Auth::user();
        $amountKES = (float) $validated['amount'];
        $phoneNumber = preg_replace('/^0/', '254', $user->phone_number);
        $channelId = config('payhero.channel_id');
        $provider = config('payhero.provider', 'm-pesa');

        // 1. Generate our unique ID
        $externalRef = 'DEPOSIT_'.$user->id.'_'.Str::random(8);

        // 2. Create the pending transaction
        $transaction = $user->transactions()->create([
            'type' => 'deposit',
            'amount' => (int) ($amountKES * 100), // Cents
            'status' => 'pending',
            'payhero_transaction_id' => $externalRef, // Save our reference
        ]);

        // 3. Dispatch the job with all necessary data
        $payload = [
            'amount' => $amountKES,
            'phone_number' => $phoneNumber,
            'channel_id' => $channelId,
            'provider' => $provider,
            'callback_url' => url('/api/webhooks/payhero'),
            'external_reference' => $externalRef, // Pass our reference
        ];

        InitiatePayHeroPayment::dispatch($transaction, $payload); // Pass the whole transaction

        return redirect()->route('dashboard')->with('success', 'STK Push initiated. Please enter your PIN.');
    }

    public function showWithdrawForm()
    {
        return view('wallet.withdraw');
    }

    public function processWithdraw(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:50|max:'.($wallet->balance / 100),
        ]);

        $amountInKES = (float) $validated['amount'];
        $amountInCents = $amountInKES * 100;
        $externalRef = 'WITHDRAW_'.$user->id.'_'.Str::random(8);

        $transaction = null; // Initialize variable

        DB::transaction(function () use ($user, $wallet, $amountInCents, $externalRef, &$transaction) {
            // 1. Debit the user's wallet
            $wallet->balance -= $amountInCents;
            $wallet->save();

            // 2. Create a 'pending' withdrawal transaction
            $transaction = $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amountInCents,
                'status' => 'pending',
                'payhero_transaction_id' => $externalRef, // Save our reference
            ]);
        });

        // 3. Dispatch payout job
        $payload = [
            'amount' => $amountInKES,
            'destination' => [
                'phone_number' => preg_replace('/^0/', '254', $user->phone_number),
            ],
            'external_reference' => $externalRef,
            'metadata' => ['user_id' => $user->id, 'type' => 'withdrawal'],
        ];

        InitiatePayHeroPayout::dispatch($transaction, $payload); // Pass the whole transaction

        return redirect()->route('dashboard')->with('success', "Your withdrawal of KES {$amountInKES} is being processed.");
    }
}
