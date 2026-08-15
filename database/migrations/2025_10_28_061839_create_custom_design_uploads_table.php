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
        Schema::create('custom_design_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_design_order_id')->constrained()->onDelete('cascade');
            $table->string('section_name'); // A, B, C, D, E, F, G, H, I, J (sesuai area cetak)
            $table->string('file_path'); // Path lengkap di storage
            $table->string('file_name'); // Nama file original
            $table->unsignedBigInteger('file_size')->nullable(); // Size dalam bytes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_design_uploads');
    }
};
