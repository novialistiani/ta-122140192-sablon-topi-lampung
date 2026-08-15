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
        Schema::create('product_custom_design_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('custom_design_price_id')->constrained('custom_design_prices')->onDelete('cascade');
            $table->decimal('custom_price', 10, 2)->nullable(); // Harga khusus untuk produk ini, null = pakai harga default
            $table->boolean('is_active')->default(true); // Enable/disable per produk
            $table->timestamps();
            
            // Unique constraint with shorter name
            $table->unique(['product_id', 'custom_design_price_id'], 'product_custom_design_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_custom_design_prices');
    }
};
