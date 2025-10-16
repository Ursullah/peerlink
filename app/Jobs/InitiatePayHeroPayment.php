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
            // Ensure amount sent to PayHero is positive (KES). Transactions store cents and can be negative for debits.
            'amount' => abs($transaction->amount) / 100,
            'external_reference' => $transaction->id . '_' . now()->timestamp,
        ]);

        $response = $payHero->initiatePayment($data);

        if ($response && $response->successful()) {
            $body = $response->json();
            // PayHero returns different identifier keys depending on the endpoint/version.
            // Prefer 'reference' or 'CheckoutRequestID', then external_reference or id.
            $payheroId = $body['reference'] ?? $body['CheckoutRequestID'] ?? $body['checkout_request_id'] ?? $body['external_reference'] ?? $body['id'] ?? $data['external_reference'] ?? null;

            // Save whatever identifier we have so webhooks can match back to this transaction.
            $transaction->payhero_transaction_id = $payheroId;
            $transaction->status = 'pending';
            $transaction->save();
        } else {
            // If we received a response, decide based on status code. Server errors (5xx) are transient — throw to allow retry.
            if ($response && $response->serverError()) {
                Log::warning('InitiatePayHeroPayment: transient server error, will retry', ['transaction' => $transaction->id, 'status' => $response->status(), 'body' => $response->body()]);
                // Throw to allow the queue worker to retry according to its retry policy
                throw new \Exception('Transient PayHero error: ' . $response->body());
            }

            // Client errors (4xx) are likely permanent — mark transaction failed and log details.
            $status = $response ? $response->status() : null;
            $body = $response ? $response->body() : 'no response';
            Log::error('InitiatePayHeroPayment: initiation failed (permanent)', ['transaction' => $transaction->id, 'status' => $status, 'body' => $body]);
            $transaction->status = 'failed';
            $transaction->save();
        }
    }
}
