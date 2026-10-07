<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'custom_design_orders'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'payment_rejection_reason')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->text('payment_rejection_reason')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['orders', 'custom_design_orders'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'payment_rejection_reason')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('payment_rejection_reason');
                });
            }
        }
    }
};
