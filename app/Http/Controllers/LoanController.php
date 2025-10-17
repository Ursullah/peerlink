<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;
use App\Jobs\InitiatePayHeroPayment;
use Illuminate\Support\Str;


class LoanController extends Controller
{
    public function repay(Loan $loan)
    {
        if ($loan->borrower_id !== auth()->id() || $loan->status !== 'active') {
            abort(403, 'Unauthorized or loan is not active for repayment.');
        }

        $borrower = $loan->borrower;
        $amountKES = $loan->total_repayable / 100; // KES
        $phoneNumber = preg_replace('/^0/', '254', $borrower->phone_number);
        $channelId = config('payhero.channel_id');
        $provider = config('payhero.provider', 'm-pesa');

        // 1. Generate our unique ID
        $externalRef = 'REPAY_' . $loan->id . '_' . Str::random(8);

        // 2. Create the pending transaction
        $transaction = $borrower->transactions()->create([
            'transactionable_id' => $loan->id,
            'transactionable_type' => Loan::class,
            'type' => 'repayment',
            'amount' => -$loan->total_repayable, // cents
            'status' => 'pending',
            'payhero_transaction_id' => $externalRef, // Save our reference
        ]);

        // 3. Dispatch the job
        $payload = [
            'amount' => $amountKES,
            'phone_number' => $phoneNumber,
            'channel_id' => $channelId,
            'provider' => $provider,
            'callback_url' => url('/api/webhooks/payhero'),
            'external_reference' => $externalRef, // Pass our reference
        ];

        InitiatePayHeroPayment::dispatch($transaction, $payload); // Pass the whole transaction

        return back()->with('success', 'Repayment initiated. Please check your phone and enter your M-Pesa PIN.');
    }
}