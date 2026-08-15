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
        // For MySQL databases, modify the enum to include 'system'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('customer', 'admin', 'system') DEFAULT 'customer'");
        }
        
        // For SQLite, no action needed as it doesn't enforce ENUM constraints
        // The validation will be handled at the application level
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        if (DB::getDriverName() === 'mysql') {
            // First, convert any 'system' values to 'admin'
            DB::table('chat_messages')->where('sender_type', 'system')->update(['sender_type' => 'admin']);
            
            // Then modify the enum
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('customer', 'admin') DEFAULT 'customer'");
        }
    }
};
