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
        Schema::create('platform_revenues', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'interest_commission', 'transaction_fee', 'late_fee', 'processing_fee'
            $table->unsignedBigInteger('source_id'); // ID of the loan, transaction, or loan request
            $table->string('source_type'); // 'App\Models\Loan', 'App\Models\Transaction', etc.
            $table->integer('amount'); // Amount in cents
            $table->decimal('percentage', 5, 2)->nullable(); // Commission percentage if applicable
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_revenues');
    }
};
