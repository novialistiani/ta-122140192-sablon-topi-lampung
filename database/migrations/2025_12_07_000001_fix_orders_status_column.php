<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL databases, ensure status column is properly sized
        if (DB::getDriverName() === 'mysql') {
            // Modify the status column to be VARCHAR(50) to accommodate all status values
            DB::statement("ALTER TABLE orders MODIFY status VARCHAR(50) DEFAULT 'pending'");
            DB::statement("ALTER TABLE custom_design_orders MODIFY status VARCHAR(50) DEFAULT 'pending'");
        }
        
        // For SQLite, no action needed as it doesn't enforce column size constraints
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is non-reversible as we're expanding column size
        // Reverting would risk data loss
    }
};
