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
        Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained();
        $table->nullableMorphs('transactionable'); // Links to Loan, LoanRequest, etc.
        $table->enum('type', ['deposit', 'withdrawal', 'collateral_lock', 'loan_funding', 'repayment', 'collateral_release']);
        $table->bigInteger('amount'); // In cents. Can be negative for debits.
        $table->string('payhero_transaction_id')->nullable()->unique(); // From the API
        $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
