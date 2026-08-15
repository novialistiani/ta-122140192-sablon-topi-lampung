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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            
            // Reference to notification (nullable karena bisa standalone email)
            $table->foreignId('notification_id')->nullable()->constrained('notifications')->onDelete('cascade');
            
            // Channel used (email, in-app, sms, etc)
            $table->enum('channel', ['email', 'in-app', 'sms', 'push'])->default('email');
            
            // Recipient info
            $table->string('recipient_type'); // User, Admin
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            
            // Email specific
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable(); // Resend message ID
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'sent',
                'delivered',
                'opened',
                'clicked',
                'bounced',
                'complained',
                'failed'
            ])->default('pending');
            
            // Timestamps for each status
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Error handling
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            
            // Metadata (JSON untuk data tambahan dari webhook)
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['recipient_type', 'recipient_id'], 'recipient_index');
            $table->index('channel');
            $table->index('status');
            $table->index('message_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
