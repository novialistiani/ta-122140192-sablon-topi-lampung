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
        // Modify chat_source enum to include 'chatbot' for unified chatbot conversations
        if (Schema::hasColumn('chat_conversations', 'chat_source')) {
            DB::statement("ALTER TABLE chat_conversations MODIFY chat_source ENUM('product_detail', 'catalog', 'all_products', 'help_page', 'chatbot_page', 'chatbot') DEFAULT 'help_page'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('chat_conversations', 'chat_source')) {
            // Update any 'chatbot' values to 'chatbot_page' before removing the enum value
            DB::statement("UPDATE chat_conversations SET chat_source = 'chatbot_page' WHERE chat_source = 'chatbot'");
            DB::statement("ALTER TABLE chat_conversations MODIFY chat_source ENUM('product_detail', 'catalog', 'all_products', 'help_page', 'chatbot_page') DEFAULT 'help_page'");
        }
    }
};
