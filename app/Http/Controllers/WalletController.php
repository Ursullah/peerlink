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
    /**
     * Show the form for depositing funds.
     */
    public function showDepositForm()
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admins cannot access wallet features.');
        }

        return view('wallet.deposit');
    }

    /**
     * Process the deposit request by initiating a PayHero STK Push.
     */
    public function processDeposit(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403);
        }

        $validated = $request->validate(['amount' => 'required|numeric|min:10']);
        $user = Auth::user();
        $amountKES = (float) $validated['amount'];
        $phoneNumber = preg_replace('/^0/', '254', $user->phone_number);

        $channelId = config('payhero.channel_id');
        $provider = config('payhero.provider', 'm-pesa');
        $externalRef = 'DEPOSIT_'.$user->id.'_'.Str::random(8);

        // **FIX:** Save the unique ID to the 'external_reference' column to match the webhook.
        $transaction = $user->transactions()->create([
            'type' => 'deposit',
            'amount' => (int) ($amountKES * 100), // Store in cents
            'status' => 'pending',
            'external_reference' => $externalRef,
        ]);

        $payload = [
            'amount' => $amountKES,
            'phone_number' => $phoneNumber,
            'channel_id' => $channelId,
            'provider' => $provider,
            'callback_url' => url('/api/webhooks/payhero'),
            'external_reference' => $externalRef,
        ];

        InitiatePayHeroPayment::dispatch($transaction, $payload);

        $successMessage = 'STK Push initiated. Please check your phone and enter your PIN.';

        if ($user->role === 'lender') {
            return redirect()->route('lender.dashboard')->with('success', $successMessage);
        } else {
            return redirect()->route('dashboard')->with('success', $successMessage);
        }
    }

    /**
     * Show the form for withdrawing funds.
     */
    public function showWithdrawForm()
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admins cannot access wallet features.');
        }

        return view('wallet.withdraw');
    }

    /**
     * Process the withdrawal request.
     */
    public function processWithdraw(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403);
        }

        $user = Auth::user();
        $wallet = $user->wallet;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:50|max:'.($wallet->balance / 100),
        ]);

        $amountInKES = (float) $validated['amount'];
        $amountInCents = $amountInKES * 100;
        $externalRef = 'WITHDRAW_'.$user->id.'_'.Str::random(8);
        $transaction = null;

        DB::transaction(function () use ($user, $wallet, $amountInCents, $externalRef, &$transaction) {
            $wallet->balance -= $amountInCents;
            $wallet->save();

            // **FIX:** Save the unique ID to the 'external_reference' column.
            $transaction = $user->transactions()->create([
                'type' => 'withdrawal',
                'amount' => -$amountInCents,
                'status' => 'pending',
                'external_reference' => $externalRef,
            ]);
        });

        if (! $transaction) {
            return back()->with('error', 'Withdrawal could not be initiated due to a database error.');
        }

        $payload = [
            'amount' => $amountInKES,
            'destination' => ['phone_number' => preg_replace('/^0/', '254', $user->phone_number)],
            'external_reference' => $externalRef,
            'metadata' => ['user_id' => $user->id, 'type' => 'withdrawal'],
            'callback_url' => url('/api/webhooks/payhero-payout'),
        ];

        InitiatePayHeroPayout::dispatch($transaction, $payload);

        $successMessage = "Your withdrawal of KES {$amountInKES} is being processed.";

        if ($user->role === 'lender') {
            return redirect()->route('lender.dashboard')->with('success', $successMessage);
        } else {
            return redirect()->route('dashboard')->with('success', $successMessage);
        }
    }
}
