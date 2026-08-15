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
        // Log all products with their current image paths
        $products = DB::table('products')->get();
        
        \Log::info('=== ANALYZING PRODUCT IMAGES ===');
        \Log::info("Total products: " . $products->count());
        
        $externalCount = 0;
        $localCount = 0;
        $nullCount = 0;
        
        foreach ($products as $product) {
            if (!$product->image) {
                $nullCount++;
            } elseif (filter_var($product->image, FILTER_VALIDATE_URL)) {
                $externalCount++;
                \Log::info("EXTERNAL: Product {$product->id} ({$product->name}): " . substr($product->image, 0, 80));
            } else {
                $localCount++;
                \Log::info("LOCAL: Product {$product->id} ({$product->name}): {$product->image}");
            }
        }
        
        \Log::info("Summary: External URLs: {$externalCount} | Local paths: {$localCount} | NULL: {$nullCount}");
        
        // Verify variant images
        $variants = DB::table('product_variants')->whereNotNull('image')->get();
        \Log::info("Product Variants with images: " . $variants->count());
        
        foreach ($variants as $variant) {
            if (filter_var($variant->image, FILTER_VALIDATE_URL)) {
                \Log::warn("EXTERNAL variant image: {$variant->image}");
            } else {
                \Log::info("LOCAL variant image: {$variant->image}");
            }
        }
        
        // Verify custom design uploads
        $uploads = DB::table('custom_design_uploads')->whereNotNull('file_path')->get();
        \Log::info("Custom design uploads: " . $uploads->count());
        
        foreach ($uploads as $upload) {
            \Log::info("Upload file path: {$upload->file_path}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is informational only - no reversible changes
    }
};
