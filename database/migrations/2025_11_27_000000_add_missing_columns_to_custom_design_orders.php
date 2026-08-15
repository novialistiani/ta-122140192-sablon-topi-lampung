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
        // Add missing columns to custom_design_orders
        Schema::table('custom_design_orders', function (Blueprint $table) {
            // Add payment_status if not exists
            if (!Schema::hasColumn('custom_design_orders', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'va_active', 'paid', 'processing', 'failed', 'refunded'])
                    ->default('unpaid')
                    ->after('status');
            }
            
            // Add processing_at if not exists
            if (!Schema::hasColumn('custom_design_orders', 'processing_at')) {
                $table->timestamp('processing_at')->nullable()->after('approved_at');
            }
            
            // Add completed_at if not exists
            if (!Schema::hasColumn('custom_design_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('processing_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_design_orders', function (Blueprint $table) {
            if (Schema::hasColumn('custom_design_orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('custom_design_orders', 'processing_at')) {
                $table->dropColumn('processing_at');
            }
            if (Schema::hasColumn('custom_design_orders', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
