<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify orders table status ENUM to include 'approved' and 'rejected'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        
        // Modify custom_design_orders table status ENUM to include 'approved' and 'rejected'
        DB::statement("ALTER TABLE custom_design_orders MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert orders table status ENUM
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        
        // Revert custom_design_orders table status ENUM
        DB::statement("ALTER TABLE custom_design_orders MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
