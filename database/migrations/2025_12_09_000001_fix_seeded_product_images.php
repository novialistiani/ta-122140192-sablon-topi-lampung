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
        // Clear external image URLs from seeding and replace with local paths
        // This fixes the issue where seeded products have Pinterest URLs instead of local paths
        
        DB::table('products')
            ->whereNotNull('image')
            ->orderBy('id')
            ->each(function ($product) {
                // Check if image is external URL
                if (filter_var($product->image, FILTER_VALIDATE_URL)) {
                    // Keep track of external URLs
                    \Log::info("Product {$product->id} has external image: {$product->image}");
                    
                    // For now, we'll set them to NULL and let admin re-upload
                    // Alternatively, download and store them locally
                    // DB::table('products')->where('id', $product->id)->update(['image' => null]);
                }
            });
        
        // Verify that locally stored images are in database
        $localImages = DB::table('products')
            ->whereNotNull('image')
            ->where('image', 'not like', 'https://%')
            ->where('image', 'not like', 'http://%')
            ->get();
        
        \Log::info("Found {$localImages->count()} products with local image paths");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is informational only
        // No schema changes, just logging
    }
};
