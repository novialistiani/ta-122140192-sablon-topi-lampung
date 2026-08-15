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
        // Clean up invalid values before changing enum
        // Update any invalid status values to 'pending'
        DB::table('orders')->whereNotIn('status', ['pending', 'approved', 'rejected', 'processing', 'completed', 'cancelled'])->update(['status' => 'pending']);
        DB::table('custom_design_orders')->whereNotIn('status', ['pending', 'approved', 'rejected', 'processing', 'completed', 'cancelled'])->update(['status' => 'pending']);

        // For SQLite, the status column already accepts string values
        // No need to modify enum - SQLite doesn't have native ENUM support
        // The validation should be handled at the application level
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clean up invalid values before reverting enum
        // Update 'approved' and 'rejected' to 'pending' before reverting
        DB::table('orders')->whereIn('status', ['approved', 'rejected'])->update(['status' => 'pending']);
        DB::table('custom_design_orders')->whereIn('status', ['approved', 'rejected'])->update(['status' => 'pending']);

        // For SQLite, no schema changes needed
    }
};
