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
        // Transaction is already loaded
        if (! $this->transaction) {
            Log::error('InitiatePayHeroPayment: transaction not found');

            return;
        }

        try {
            $response = $payHero->initiatePayment($this->payload);

            if ($response && $response->successful()) {
                $body = $response->json();

                // Get the real PayHero ID
                $payheroId = $body['id'] ?? $body['reference'] ?? $body['CheckoutRequestID'] ?? null;

                if ($payheroId) {
                    // Update our transaction to store the REAL PayHero ID
                    $this->transaction->payhero_transaction_id = $payheroId;
                    $this->transaction->save();
                    Log::info("PayHero payment initiated for Tx: {$this->transaction->id}", ['payhero_id' => $payheroId]);
                } else {
                    Log::warning("PayHero response missing ID for Tx: {$this->transaction->id}", ['body' => $body]);
                }
            } else {
                // API call failed
                $status = $response ? $response->status() : null;
                $body = $response ? $response->body() : 'no response';
                Log::error('InitiatePayHeroPayment: initiation failed', ['transaction' => $this->transaction->id, 'status' => $status, 'body' => $body]);
                $this->transaction->status = 'failed';
                $this->transaction->save();

                if ($response && $response->serverError()) {
                    throw new \Exception('Transient PayHero error: '.$response->body());
                }
            }
        } catch (\Throwable $ex) {
            Log::error('InitiatePayHeroPayment: job exception', ['message' => $ex->getMessage()]);
            $this->transaction->status = 'failed';
            $this->transaction->save();
            throw $ex;
        }
    }
}
