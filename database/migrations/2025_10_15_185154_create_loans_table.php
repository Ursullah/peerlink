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
        Schema::create('loans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('loan_request_id')->constrained()->onDelete('cascade');
        $table->foreignId('borrower_id')->references('id')->on('users');
        $table->foreignId('lender_id')->references('id')->on('users');
        $table->bigInteger('principal_amount'); // In cents
        $table->bigInteger('interest_amount'); // In cents
        $table->bigInteger('total_repayable'); // In cents
        $table->bigInteger('amount_repaid')->default(0);
        $table->enum('status', ['active', 'repaid', 'defaulted'])->default('active');
        $table->timestamp('due_date');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
