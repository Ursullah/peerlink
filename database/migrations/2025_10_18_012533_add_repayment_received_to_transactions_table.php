<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- 1. ADD THIS LINE

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Only run this raw SQL statement if the connection is MySQL
        if (DB::Connection()->getDriverName() == 'mysql') {
            DB::statement("ALTER TABLE transactions CHANGE COLUMN type type ENUM('deposit', 'withdrawal', 'collateral_lock', 'loan_funding', 'repayment', 'collateral_release', 'repayment_received', 'loan_disbursement') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // <-- 2. \ CHECK AROUND THE 'down' LOGIC -->
        if (DB::Connection()->getDriverName() == 'mysql') {
            // Revert the ENUM list back to the original
            DB::statement("ALTER TABLE transactions CHANGE COLUMN type type ENUM('deposit', 'withdrawal', 'collateral_lock', 'loan_funding', 'repayment', 'collateral_release') NOT NULL");
        }
    }
};