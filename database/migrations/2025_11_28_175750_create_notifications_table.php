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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relation to notifiable (User, Admin, etc)
            $table->string('notifiable_type'); // User, Admin
            $table->unsignedBigInteger('notifiable_id');
            $table->index(['notifiable_type', 'notifiable_id'], 'notifiable_index');
            
            // Notification details
            $table->string('type'); // order_created, payment_received, etc
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Extra data (order_id, amount, etc)
            
            // Action URL (optional)
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            
            // Priority level
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Status tracking
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('type');
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
