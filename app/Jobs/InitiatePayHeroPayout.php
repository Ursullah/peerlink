<?php

namespace App\Jobs;

use App\Services\PayHeroService;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Import DB for refund transaction

class InitiatePayHeroPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction; // Changed from transactionId
    public $payload;

    // Configure attempts and backoff
    public $tries = 3;
    // Example: Retry after 1, 2, 5 minutes
    public $backoff = [60, 120, 300]; 

    // Changed constructor to accept the Transaction model
    public function __construct(Transaction $transaction, array $payload = [])
    {
        $this->transaction = $transaction;
        $this->payload = $payload;
    }

    public function handle(PayHeroService $payHero)
    {
        // Transaction is already loaded
        if (!$this->transaction) {
            Log::error('InitiatePayHeroPayout: transaction model not provided.');
            return;
        }

        // Ensure job only runs on pending transactions
        if ($this->transaction->status !== 'pending') {
            Log::warning('InitiatePayHeroPayout: Transaction already processed or failed.', ['transaction_id' => $this->transaction->id]);
            return;
        }

        try {
            Log::info("Initiating PayHero Payout Job for Tx: {$this->transaction->id}");
            
            // --- THIS IS THE REAL API CALL ---
            $response = $payHero->initiatePayout($this->payload);

            if ($response && $response->successful()) {
                $body = $response->json();
                
                // Update our transaction ID with the real ID from PayHero
                $this->transaction->payhero_transaction_id = $body['id'] ?? $this->payload['external_reference'];
                // Status remains 'pending' until webhook confirmation
                $this->transaction->save(); 
                Log::info("PayHero Payout initiated successfully for Tx: {$this->transaction->id}", ['payhero_id' => $this->transaction->payhero_transaction_id]);

            } else {
                // API call failed - Log, fail & refund.
                $status = $response ? $response->status() : 'N/A';
                $body = $response ? $response->body() : 'No response';
                Log::error('InitiatePayHeroPayout: API call failed', ['transaction' => $this->transaction->id, 'status' => $status, 'body' => $body]);
                
                $this->failPayout(); // Calls the method to fail and refund

                // If it was a server error, re-throw to allow retries
                if ($response && $response->serverError()) {
                    throw new \Exception('Transient PayHero Payout error: ' . $body);
                }
            }
        } catch (\Throwable $ex) {
            Log::error('InitiatePayHeroPayout: job exception', ['message' => $ex->getMessage()]);
            $this->failPayout(); // Also fail and refund on general exceptions
            throw $ex; // Re-throw to allow retries
        }
    }

    /**
     * Handle job failure: Mark transaction failed and refund user wallet.
     */
    protected function failPayout(): void
    {
        // Check status again inside the method for safety
        if ($this->transaction && $this->transaction->status === 'pending') {
            DB::transaction(function () {
                $this->transaction->update(['status' => 'failed']);
                
                // Refund the user's wallet
                $user = $this->transaction->user;
                // Add back the positive amount (since transaction->amount is negative)
                $user->wallet->balance += abs($this->transaction->amount); 
                $user->wallet->save(); 

                Log::warning("Payout failed for Tx: {$this->transaction->id}. User wallet refunded.");
            });
        }
    }
}