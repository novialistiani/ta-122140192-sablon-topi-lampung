<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_design_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_design_order_id')->index();
            $table->string('type', 30); // admin_request | customer_upload
            $table->text('note')->nullable();
            $table->string('section_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('previous_file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_design_revisions');
    }
};