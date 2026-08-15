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
        // Modify chat_source enum to include 'chatbot_page'
        // MySQL requires us to modify the column to add new enum value
        if (Schema::hasColumn('chat_conversations', 'chat_source')) {
            DB::statement("ALTER TABLE chat_conversations MODIFY chat_source ENUM('product_detail', 'catalog', 'all_products', 'help_page', 'chatbot_page') DEFAULT 'help_page'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        if (Schema::hasColumn('chat_conversations', 'chat_source')) {
            DB::statement("ALTER TABLE chat_conversations MODIFY chat_source ENUM('product_detail', 'catalog', 'all_products', 'help_page') DEFAULT 'help_page'");
        }
    }
};
