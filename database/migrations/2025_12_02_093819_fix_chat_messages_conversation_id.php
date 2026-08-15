<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if we need to add conversation_id column
        if (!Schema::hasColumn('chat_messages', 'conversation_id') && Schema::hasColumn('chat_messages', 'chat_conversation_id')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                // Add new conversation_id column
                $table->unsignedBigInteger('conversation_id')->nullable()->after('id');
            });

            // Copy data from chat_conversation_id to conversation_id
            DB::statement('UPDATE chat_messages SET conversation_id = chat_conversation_id WHERE conversation_id IS NULL');

            // Add foreign key constraint
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->foreign('conversation_id')
                    ->references('id')
                    ->on('chat_conversations')
                    ->onDelete('cascade');
            });
        }

        // If conversation_id exists but doesn't have foreign key, add it
        if (Schema::hasColumn('chat_messages', 'conversation_id')) {
            try {
                Schema::table('chat_messages', function (Blueprint $table) {
                    // Try to add foreign key if not exists
                    $table->foreign('conversation_id')
                        ->references('id')
                        ->on('chat_conversations')
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, continue
            }
        }
    }

    public function down()
    {
        // Don't drop the column on rollback to avoid data loss
    }
};
