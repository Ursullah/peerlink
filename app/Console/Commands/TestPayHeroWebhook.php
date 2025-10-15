<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestPayHeroWebhook extends Command
{
    protected $signature = 'test:payhero-webhook {transaction_id}';
    protected $description = 'Simulates a successful PayHero webhook call for a pending transaction.';

    public function handle()
    {
        $transactionId = $this->argument('transaction_id');
        $transaction = Transaction::where('payhero_transaction_id', $transactionId)->where('status', 'pending')->first();

        if (!$transaction) {
            $this->error("No pending transaction found with PayHero ID: {$transactionId}");
            return 1;
        }

        $this->info("Found pending transaction. Simulating webhook...");

        // This is the data PayHero would send us.
        $payload = [
            'transaction_id' => $transaction->payhero_transaction_id,
            'status' => 'SUCCESSFUL',
            'amount' => $transaction->amount / 100, // PayHero sends amount in KES
            'phone_number' => $transaction->user->phone_number,
        ];

        // We use Laravel's HTTP client to send a request to our own app,
        // just like PayHero would.
        $response = Http::post('http://127.0.0.1:8000/api/webhooks/payhero', $payload);

        if ($response->successful()) {
            $this->info('Webhook simulation sent successfully!');
            $this->info('Response: ' . $response->body());
        } else {
            $this->error('Failed to send webhook simulation.');
            $this->error('Response Status: ' . $response->status());
            $this->error('Response Body: ' . $response->body());
        }

        return 0;
    }
}