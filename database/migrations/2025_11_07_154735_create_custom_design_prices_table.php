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
        Schema::create('custom_design_prices', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'upload_section' or 'cutting_type'
            $table->string('code')->unique(); // A, B, C, etc or cutting-pvc-flex, printable
            $table->string('name'); // Full name/description
            $table->decimal('price', 10, 2)->default(0); // Price in Rupiah
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_design_prices');
    }
};
