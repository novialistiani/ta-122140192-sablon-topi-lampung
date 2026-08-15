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
        Schema::table('notification_logs', function (Blueprint $table) {
            // Add Resend email ID for webhook tracking
            $table->string('resend_email_id')->nullable()->after('message_id');
            
            // Add index for faster webhook lookups
            $table->index('resend_email_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['resend_email_id']);
            $table->dropColumn('resend_email_id');
        });
    }
};
