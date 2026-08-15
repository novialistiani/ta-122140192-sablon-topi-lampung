<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add columns ke chat_conversations untuk escalation dan admin takeover
        Schema::table('chat_conversations', function (Blueprint $table) {
            // Escalation tracking
            $table->boolean('is_escalated')->default(false)->comment('Konversasi membutuhkan escalation ke admin');
            $table->timestamp('escalated_at')->nullable()->comment('Waktu konversasi di-escalate');
            $table->text('escalation_reason')->nullable()->comment('Alasan escalation');
            
            // Admin takeover
            $table->boolean('taken_over_by_admin')->default(false)->comment('Admin sudah mengambil alih konversasi');
            $table->timestamp('taken_over_at')->nullable()->comment('Waktu admin ambil alih');
            
            // Needs response trigger
            $table->boolean('needs_admin_response')->default(false)->comment('Menandakan customer menunggu jawaban admin');
            $table->timestamp('needs_response_since')->nullable()->comment('Waktu customer mengaktifkan trigger');
        });
        
        // Add columns ke chat_messages
        Schema::table('chat_messages', function (Blueprint $table) {
            // Escalation status di message level
            $table->boolean('is_escalated')->default(false)->comment('Pesan yang trigger escalation');
        });
    }

    public function down()
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn('is_escalated');
            $table->dropColumn('escalated_at');
            $table->dropColumn('escalation_reason');
            $table->dropColumn('taken_over_by_admin');
            $table->dropColumn('taken_over_at');
            $table->dropColumn('needs_admin_response');
            $table->dropColumn('needs_response_since');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('is_escalated');
        });
    }
};
