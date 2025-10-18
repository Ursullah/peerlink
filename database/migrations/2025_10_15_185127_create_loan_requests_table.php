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
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The borrower
            $table->bigInteger('amount'); // Requested amount in cents
            $table->integer('repayment_period'); // In days (e.g., 30, 60)
            $table->decimal('interest_rate', 5, 2); // e.g., 15.00 for 15%
            $table->text('reason');
            $table->bigInteger('collateral_locked'); // Collateral amount in cents
            $table->enum('status', ['pending_approval', 'active', 'funded', 'rejected'])->default('pending_approval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
