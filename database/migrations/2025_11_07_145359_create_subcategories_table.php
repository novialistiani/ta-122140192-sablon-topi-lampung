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
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama subcategory (readable): "Baby Jumper"
            $table->text('description')->nullable(); // Description for the subcategory
            $table->string('slug')->default('uncategorized')->unique(); // Slug format: "baby-jumper"
            $table->string('category')->default('lainnya'); // Category: "lainnya"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};
