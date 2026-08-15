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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->default(uniqid())->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('virtual_account_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_type')->nullable(); // 'custom' or 'regular'
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('payment_method')->default('bank_transfer'); // 'va', 'ewallet', etc
            $table->string('payment_channel')->nullable(); // 'bri', 'bca', 'gopay', etc
            $table->decimal('amount', 15, 2);
            $table->string('status'); // 'pending', 'paid', 'failed', 'expired'
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['transaction_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
