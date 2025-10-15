<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayHeroWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. SECURITY: Verify the webhook signature (simulation)
        // In a real app, you'd compare a signature from the request header
        // with one you generate using your webhook secret to ensure it's from PayHero.
        $webhookSecret = config('app.payhero_webhook_secret');
        // if (! $this->isSignatureValid($request, $webhookSecret)) {
        //     Log::warning('Invalid PayHero webhook signature received.');
        //     return response()->json(['error' => 'Invalid signature'], 403);
        // }
        
        $payload = $request->all();
        Log::info('PayHero Webhook Received:', $payload); // Good for debugging

        // 2. Find the corresponding transaction in our database
        $transaction = Transaction::where('payhero_transaction_id', $payload['transaction_id'])->first();

        if (!$transaction) {
            Log::warning('PayHero webhook for unknown transaction received.', $payload);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // 3. Check if we've already processed this transaction
        if ($transaction->status !== 'pending') {
            Log::info('PayHero webhook for already processed transaction received.', $payload);
            return response()->json(['message' => 'Webhook already processed']);
        }
        
        // 4. Process the payment status
        if ($payload['status'] === 'SUCCESSFUL') {
            DB::transaction(function () use ($transaction) {
                // Update the transaction status
                $transaction->update(['status' => 'successful']);

                // UPDATE THE USER'S WALLET BALANCE
                $user = $transaction->user;
                $user->wallet->balance += $transaction->amount;
                $user->wallet->save();
            });
        } else {
            // If payment failed, just update the status
            $transaction->update(['status' => 'failed']);
        }

        // 5. Respond to PayHero to acknowledge receipt
        return response()->json(['message' => 'Webhook processed successfully']);
    }
}