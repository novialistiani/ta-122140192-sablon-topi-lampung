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
        // Modify sender_type enum to include 'bot' and 'user'
        if (Schema::hasColumn('chat_messages', 'sender_type')) {
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('customer', 'admin', 'bot', 'user') DEFAULT 'customer'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('chat_messages', 'sender_type')) {
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('customer', 'admin') DEFAULT 'customer'");
        }
    }
};
