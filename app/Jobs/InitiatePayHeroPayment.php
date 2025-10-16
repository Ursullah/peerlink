<?php

namespace App\Jobs;

use App\Services\PayHeroService;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Logging\Log as LogContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class InitiatePayHeroPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transactionId;
    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(int $transactionId, array $payload = [])
    {
        $this->transactionId = $transactionId;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(PayHeroService $payHero)
    {
        $transaction = Transaction::find($this->transactionId);
        if (! $transaction) {
            Log::error('InitiatePayHeroPayment: transaction not found', ['transaction_id' => $this->transactionId]);
            return;
        }

        // Merge defaults
        $data = array_merge($this->payload, [
            'amount' => $transaction->amount / 100, // convert cents to KES
            'external_reference' => $transaction->id . '_' . now()->timestamp,
        ]);

        $response = $payHero->initiatePayment($data);

        if ($response->successful()) {
            $body = $response->json();
            $transaction->payhero_transaction_id = $body['id'] ?? $data['external_reference'];
            $transaction->status = 'pending';
            $transaction->save();
        } else {
            Log::error('InitiatePayHeroPayment: initiation failed', ['transaction' => $transaction->id, 'status' => $response->status(), 'body' => $response->body()]);
            // Optionally mark transaction as failed or retry depending on your logic
            $transaction->status = 'failed';
            $transaction->save();
        }
    }
}
