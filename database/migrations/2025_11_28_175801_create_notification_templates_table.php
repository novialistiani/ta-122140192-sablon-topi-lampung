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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            
            // Template identification
            $table->string('type')->unique(); // order_created, payment_received, etc
            $table->string('name'); // Display name for admin
            $table->text('description')->nullable();
            
            // Channel configuration
            $table->enum('channel', ['email', 'in-app', 'sms', 'push'])->default('email');
            
            // Email template (for email channel)
            $table->string('subject')->nullable();
            $table->text('template'); // Blade view path or HTML content
            
            // In-app template (for in-app channel)
            $table->string('title_template')->nullable();
            $table->text('message_template')->nullable();
            
            // Available variables (JSON array)
            $table->json('available_variables')->nullable(); // ['order_id', 'customer_name', 'amount']
            
            // Action configuration
            $table->string('action_url_template')->nullable(); // /orders/{order_id}
            $table->string('action_text')->nullable(); // View Order
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('channel');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
