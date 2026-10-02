   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Support\Facades\DB;

   return new class extends Migration
   {
       public function up(): void
       {
           DB::statement("ALTER TABLE custom_design_orders MODIFY payment_status VARCHAR(50) DEFAULT 'unpaid'");
       }

       public function down(): void
       {
           DB::statement("ALTER TABLE custom_design_orders MODIFY payment_status ENUM('unpaid', 'va_active', 'paid', 'processing', 'failed', 'refunded') DEFAULT 'unpaid'");
       }
   };