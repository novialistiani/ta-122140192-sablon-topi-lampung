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
        Schema::table('orders', function (Blueprint $table) {
            // Kolom untuk tracking deadline konfirmasi admin (24 jam dari order dibuat)
            $table->datetime('confirmation_deadline')->nullable()->after('payment_deadline');
            
            // Index untuk query order yang sudah melewati deadline
            $table->index('confirmation_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['confirmation_deadline']);
            $table->dropColumn('confirmation_deadline');
        });
    }
};
