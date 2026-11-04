<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('variant_label')->nullable();
            $table->string('type', 20);            // in, out, adjustment
            $table->integer('quantity_change');    // positif = masuk, negatif = keluar
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reason', 40);
            $table->string('reference_type', 30)->nullable(); // order, custom_order
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};