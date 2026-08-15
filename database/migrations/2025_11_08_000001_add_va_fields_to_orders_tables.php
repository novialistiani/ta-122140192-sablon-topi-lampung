<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'va_number')) {
                $table->string('va_number')->nullable();
            }
            if (!Schema::hasColumn('orders', 'va_generated_at')) {
                $table->timestamp('va_generated_at')->nullable();
            }
        });

        Schema::table('custom_design_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_design_orders', 'va_number')) {
                $table->string('va_number')->nullable();
            }
            if (!Schema::hasColumn('custom_design_orders', 'va_generated_at')) {
                $table->timestamp('va_generated_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['va_number', 'va_generated_at']);
        });

        Schema::table('custom_design_orders', function (Blueprint $table) {
            $table->dropColumn(['va_number', 'va_generated_at']);
        });
    }
};