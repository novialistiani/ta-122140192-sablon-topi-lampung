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
        Schema::table('users', function (Blueprint $table) {
            // Alamat lengkap - Required fields
            $table->text('address')->nullable()->after('phone');
            $table->string('province')->nullable()->after('address');
            $table->string('city')->nullable()->after('province');
            $table->string('district')->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('district');
            $table->text('address_notes')->nullable()->after('postal_code');
            
            // Optional profile fields
            $table->enum('gender', ['male', 'female'])->nullable()->after('address_notes');
            $table->date('birth_date')->nullable()->after('gender');
            $table->text('bio')->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
