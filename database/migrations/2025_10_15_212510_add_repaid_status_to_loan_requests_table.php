<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // Use Schema Builder for better compatibility
    Schema::table('loan_requests', function (Blueprint $table) {
        // Change the column type to string, which SQLite handles easily
        $table->string('status')->default('pending_approval')->change(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the ENUM list back to the original
        DB::statement("ALTER TABLE loan_requests CHANGE COLUMN status status ENUM('pending_approval', 'active', 'funded', 'rejected') NOT NULL DEFAULT 'pending_approval'");
    }
};