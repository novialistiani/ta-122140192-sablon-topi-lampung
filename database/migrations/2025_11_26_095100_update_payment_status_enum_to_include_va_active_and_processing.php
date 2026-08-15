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
        // Update orders table payment_status enum to include 'va_active' and 'processing'
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'va_active', 'paid', 'processing', 'failed', 'refunded') DEFAULT 'unpaid'");
        });

        // Update custom_design_orders table - check if it has payment_status column first
        if (Schema::hasColumn('custom_design_orders', 'payment_status')) {
            Schema::table('custom_design_orders', function (Blueprint $table) {
                DB::statement("ALTER TABLE custom_design_orders MODIFY payment_status ENUM('unpaid', 'va_active', 'paid', 'processing', 'failed', 'refunded') DEFAULT 'unpaid'");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'paid', 'failed', 'refunded') DEFAULT 'unpaid'");
        });

        if (Schema::hasColumn('custom_design_orders', 'payment_status')) {
            Schema::table('custom_design_orders', function (Blueprint $table) {
                DB::statement("ALTER TABLE custom_design_orders MODIFY payment_status ENUM('unpaid', 'paid', 'failed', 'refunded') DEFAULT 'unpaid'");
            });
        }
    }
};
