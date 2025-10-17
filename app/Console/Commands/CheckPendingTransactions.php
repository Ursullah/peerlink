<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-pending-transactions'; // Updated signature

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks old pending transactions as failed (timeout)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeoutMinutes = 5; // Define the timeout period
        $cutoffTime = Carbon::now()->subMinutes($timeoutMinutes);

        $this->info("Checking for pending transactions older than {$timeoutMinutes} minutes...");
        Log::info('Running CheckPendingTransactions command.');

        // Find transactions that are 'pending' and older than the cutoff time
        $timedOutTransactions = Transaction::where('status', 'pending')
                                           ->where('created_at', '<', $cutoffTime)
                                           ->get();

        if ($timedOutTransactions->isEmpty()) {
            $this->info('No timed-out pending transactions found.');
            return 0;
        }

        $this->warn("Found {$timedOutTransactions->count()} timed-out pending transaction(s). Marking as failed...");

        foreach ($timedOutTransactions as $transaction) {
            $transaction->update(['status' => 'failed']);
            Log::warning("Transaction #{$transaction->id} timed out and marked as failed.");
            $this->line(" - Marked transaction #{$transaction->id} as failed.");
        }

        $this->info('Finished processing timed-out transactions.');
        return 0;
    }
}