<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('customer', 'admin', 'bot', 'system', 'user') DEFAULT 'customer'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Map non-supported types back to admin
            DB::table('chat_messages')->whereIn('sender_type', ['bot', 'system', 'user'])->update(['sender_type' => 'admin']);
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('customer', 'admin') DEFAULT 'customer'");
        }
    }
};
