<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create chat_conversations table if it doesn't exist
        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('subject')->nullable();
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->json('keywords')->nullable()->comment('Selected category/keywords by user');
                $table->unsignedBigInteger('subcategory_id')->nullable()->comment('Reference to subcategories');
                $table->enum('chat_source', ['product_detail', 'catalog', 'all_products', 'help_page'])->default('help_page');
                $table->boolean('is_admin_active')->default(false)->comment('Toggle bot auto-response vs manual admin reply');
                $table->timestamp('expires_at')->nullable()->index()->comment('Auto-delete after 3 days');
                $table->unsignedBigInteger('admin_id')->nullable()->comment('Reference to admins');
                $table->timestamps();
            });
        }

        // Create chat_messages table if it doesn't exist
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_conversation_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->text('message');
                $table->enum('sender_type', ['customer', 'admin'])->default('customer');
                $table->boolean('is_admin_reply')->default(false);
                $table->boolean('is_read_by_user')->default(false);
                $table->boolean('is_read_by_admin')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
