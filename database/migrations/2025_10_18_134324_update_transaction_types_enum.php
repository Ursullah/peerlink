<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the transaction types enum to include all the types we use
        if (DB::connection()->getDriverName() == 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'deposit', 
                'withdrawal', 
                'collateral_lock', 
                'loan_funding', 
                'repayment', 
                'collateral_release', 
                'repayment_received', 
                'loan_disbursement',
                'partial_repayment',
                'stk_repayment',
                'loan_repayment_credit',
                'transaction_fee'
            ) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() == 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'deposit', 
                'withdrawal', 
                'collateral_lock', 
                'loan_funding', 
                'repayment', 
                'collateral_release'
            ) NOT NULL");
        }
    }
};
