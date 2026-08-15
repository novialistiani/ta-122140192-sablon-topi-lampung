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
        // Add payment_deadline to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('payment_deadline')->nullable()->after('approved_at');
        });

        // Add payment_deadline to custom_design_orders table
        Schema::table('custom_design_orders', function (Blueprint $table) {
            $table->timestamp('payment_deadline')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_deadline');
        });

        Schema::table('custom_design_orders', function (Blueprint $table) {
            $table->dropColumn('payment_deadline');
        });
    }
};
