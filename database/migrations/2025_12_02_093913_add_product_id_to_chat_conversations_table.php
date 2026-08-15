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
        // Only add column if it doesn't exist
        if (!Schema::hasColumn('chat_conversations', 'product_id')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->after('user_id');
            });
        }

        // Check if foreign key exists
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'chat_conversations' 
            AND COLUMN_NAME = 'product_id' 
            AND REFERENCED_TABLE_NAME = 'products'
        ");

        if (empty($foreignKeyExists)) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            });
        }

        // Check if composite index exists
        $indexExists = DB::select("
            SHOW INDEX FROM chat_conversations 
            WHERE Key_name = 'chat_conversations_user_id_product_id_status_index'
        ");

        if (empty($indexExists)) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->index(['user_id', 'product_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check and drop foreign key if exists
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'chat_conversations' 
            AND COLUMN_NAME = 'product_id' 
            AND REFERENCED_TABLE_NAME = 'products'
        ");

        if (!empty($foreignKeyExists)) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        // Check and drop index if exists
        $indexExists = DB::select("
            SHOW INDEX FROM chat_conversations 
            WHERE Key_name = 'chat_conversations_user_id_product_id_status_index'
        ");

        if (!empty($indexExists)) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'product_id', 'status']);
            });
        }

        // Drop column if exists
        if (Schema::hasColumn('chat_conversations', 'product_id')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }
    }
};
