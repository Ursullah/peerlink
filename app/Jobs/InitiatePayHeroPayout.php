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

class InitiatePayHeroPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transactionId;
    public $payload;

    // Configure attempts and backoff
    public $tries = 3;
    public $backoff = 60;

    public function __construct(int $transactionId, array $payload = [])
    {
        $this->transactionId = $transactionId;
        $this->payload = $payload;
    }

    public function handle(PayHeroService $payHero)
    {
        $transaction = Transaction::find($this->transactionId);
        if (! $transaction) {
            Log::error('InitiatePayHeroPayout: transaction not found', ['transaction_id' => $this->transactionId]);
            return;
        }

        try {
            $response = $payHero->initiatePayout($this->payload);

            if ($response->successful()) {
                $body = $response->json();
                $transaction->payhero_transaction_id = $body['id'] ?? $transaction->id;
                $transaction->status = 'pending';
                $transaction->save();
            } else {
                Log::error('InitiatePayHeroPayout: initiation failed', ['status' => $response->status(), 'body' => $response->body()]);
                $transaction->status = 'failed';
                $transaction->save();
                // Optionally throw to allow retry
            }
        } catch (\Throwable $ex) {
            Log::error('InitiatePayHeroPayout: exception', ['message' => $ex->getMessage()]);
            // Let the job be retried by rethrowing
            throw $ex;
        }
    }
}
