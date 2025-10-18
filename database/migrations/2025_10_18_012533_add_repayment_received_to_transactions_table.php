<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

public function up(): void
{
    // Add 'repayment_received' and other types to the ENUM list
    DB::statement("ALTER TABLE transactions CHANGE COLUMN type type ENUM('deposit', 'withdrawal', 'collateral_lock', 'loan_funding', 'repayment', 'collateral_release', 'repayment_received', 'loan_disbursement') NOT NULL");
}
public function down(): void
{
    // Revert the ENUM list back to the original
    DB::statement("ALTER TABLE transactions CHANGE COLUMN type type ENUM('deposit', 'withdrawal', 'collateral_lock', 'loan_funding', 'repayment', 'collateral_release') NOT NULL");
}
};
