<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\PayHeroService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InitiatePayHeroPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction;
    public $payload;

    public function __construct(Transaction $transaction, array $payload = [])
    {
        $this->transaction = $transaction;
        $this->payload = $payload;
    }

    public function handle(PayHeroService $payHero)
    {
        // --- ADDED: Log to confirm the job is starting ---
        Log::info("Job starting for transaction #{$this->transaction->id}");

        try {
            $response = $payHero->initiatePayment($this->payload);

            if ($response && $response->successful()) {
                $body = $response->json();
                $payheroId = $body['id'] ?? $body['reference'] ?? $body['CheckoutRequestID'] ?? null;

                if ($payheroId) {
                    // Update our transaction to store the REAL PayHero ID
                    $this->transaction->payhero_transaction_id = $payheroId;
                    $this->transaction->save();
                    Log::info("PayHero payment initiated successfully for Tx #{$this->transaction->id}", ['payhero_id' => $payheroId]);
                } else {
                    Log::warning("PayHero response was successful but missing an ID for Tx #{$this->transaction->id}", ['body' => $body]);
                }
            } else {
                // --- IMPROVED ERROR HANDLING ---
                $status = $response ? $response->status() : 'N/A';
                $body = $response ? $response->json() : ['error' => 'No response from PayHero.'];
                $errorMessage = $body['message'] ?? 'API call failed.';

                Log::error("PayHero initiation failed for Tx #{$this->transaction->id}", ['status' => $status, 'body' => $body]);
                
                // Update the transaction to show the failure on the dashboard
                $this->transaction->status = 'failed';
                $this->transaction->failure_reason = "API Error (Status: {$status}): {$errorMessage}";
                $this->transaction->save();

                // If it's a server error, allow the job to be retried
                if ($response && $response->serverError()) {
                    throw new \Exception("Transient PayHero error: ".($response->body()));
                }
            }
        } catch (\Throwable $ex) {
            // --- IMPROVED EXCEPTION HANDLING ---
            Log::error("Job exception for Tx #{$this->transaction->id}", ['message' => $ex->getMessage()]);
            
            // Update the transaction to show the failure
            $this->transaction->status = 'failed';
            $this->transaction->failure_reason = "Job Exception: Please check system logs.";
            $this->transaction->save();

            // Re-throw to allow Laravel's queue to handle retries/failures
            throw $ex;
        }
    }
}
