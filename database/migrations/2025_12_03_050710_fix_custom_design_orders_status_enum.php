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
        // For MySQL, we need to ALTER the ENUM to include 'approved' and 'rejected'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE custom_design_orders MODIFY status ENUM('pending', 'approved', 'rejected', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");
        }
        // SQLite doesn't have native ENUM support, so no changes needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        if (DB::getDriverName() === 'mysql') {
            // First update any 'approved' or 'rejected' to 'pending'
            DB::table('custom_design_orders')->whereIn('status', ['approved', 'rejected'])->update(['status' => 'pending']);
            DB::statement("ALTER TABLE custom_design_orders MODIFY status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");
        }
    }
};
