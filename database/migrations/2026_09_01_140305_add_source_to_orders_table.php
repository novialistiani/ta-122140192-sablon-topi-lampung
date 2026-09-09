<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->default('online')->after('status');
            $table->string('pos_payment_method')->nullable()->after('source');
            $table->unsignedBigInteger('cashier_id')->nullable()->after('pos_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['source', 'pos_payment_method', 'cashier_id']);
        });
    }
};