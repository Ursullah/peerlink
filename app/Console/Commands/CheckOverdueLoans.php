<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckOverdueLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-overdue-loans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue loans...');
        Log::info('Running CheckOverdueLoans command.');

        // Find all active loans where the due date has passed
        $overdueLoans = Loan::where('status', 'active')
            ->where('due_date', '<', Carbon::now())
            ->get();

        if ($overdueLoans->isEmpty()) {
            $this->info('No overdue loans found.');
            Log::info('No overdue loans found.');

            return 0;
        }

        $this->info("Found {$overdueLoans->count()} overdue loan(s). Processing penalties...");

        foreach ($overdueLoans as $loan) {
            $borrower = $loan->borrower;

            // Apply penalties
            $loan->status = 'defaulted';
            $borrower->reputation_score -= 50; // A significant penalty for defaulting

            $loan->save();
            $borrower->save();

            $this->warn("Loan #{$loan->id} for {$borrower->name} has been marked as defaulted. Reputation score reduced.");
            Log::warning("Loan #{$loan->id} for {$borrower->name} defaulted. Reputation reduced by 50.");
        }

        $this->info('Finished processing overdue loans.');

        return 0;
    }
}
