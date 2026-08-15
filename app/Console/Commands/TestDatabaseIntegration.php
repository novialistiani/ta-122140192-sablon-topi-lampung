<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\CustomDesignUpload;
use Illuminate\Support\Facades\Storage;

class TestDatabaseIntegration extends Command
{
    protected $signature = 'test:db-integration';
    protected $description = 'Test database image integration - check if all images are properly stored and accessible';

    public function handle()
    {
        $this->info('🔍 DATABASE IMAGE INTEGRATION TEST');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');
        
        // Test 1: Check database connectivity
        $this->testDatabaseConnection();
        
        // Test 2: Check product images
        $this->testProductImages();
        
        // Test 3: Check variant images
        $this->testVariantImages();
        
        // Test 4: Check custom design uploads
        $this->testCustomDesignUploads();
        
        // Test 5: Check storage directory
        $this->testStorageDirectories();
        
        // Test 6: Image URL integrity
        $this->testImageUrlIntegrity();
        
        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ Database integration test complete!');
    }

    private function testDatabaseConnection()
    {
        $this->info('TEST 1️⃣  DATABASE CONNECTION');
        
        try {
            $count = Product::count();
            $this->line("✅ Database connected");
            $this->line("   Products count: {$count}");
        } catch (\Exception $e) {
            $this->error("❌ Database connection failed: " . $e->getMessage());
        }
        $this->line('');
    }

    private function testProductImages()
    {
        $this->info('TEST 2️⃣  PRODUCT IMAGES');
        
        $total = Product::count();
        $withImage = Product::whereNotNull('image')->count();
        $withImages = Product::whereNotNull('images')->count();
        $localImages = Product::whereNotNull('image')
            ->where('image', 'not like', 'https://%')
            ->where('image', 'not like', 'http://%')
            ->count();
        $externalImages = $withImage - $localImages;
        
        $this->line("Total products: {$total}");
        $this->line("With 'image' field: {$withImage}");
        $this->line("  - Local paths: {$localImages} ✅");
        $this->line("  - External URLs: {$externalImages} ⚠️");
        $this->line("With 'images' (array): {$withImages}");
        
        // Show samples
        if ($localImages > 0) {
            $this->line("\n📦 Sample local product images:");
            $products = Product::whereNotNull('image')
                ->where('image', 'not like', 'https://%')
                ->limit(3)
                ->get(['id', 'name', 'image']);
            
            foreach ($products as $product) {
                $exists = file_exists(storage_path('app/public/' . $product->image));
                $status = $exists ? '✅' : '❌';
                $this->line("  {$status} {$product->name}");
                $this->line("     Path: {$product->image}");
            }
        }
        
        if ($externalImages > 0) {
            $this->warn("\n🌐 Sample external image URLs:");
            $products = Product::whereNotNull('image')
                ->where(function($q) {
                    $q->where('image', 'like', 'https://%')
                      ->orWhere('image', 'like', 'http://%');
                })
                ->limit(2)
                ->get(['id', 'name', 'image']);
            
            foreach ($products as $product) {
                $this->line("  ⚠️ {$product->name}");
                $this->line("     URL: {$product->image}");
            }
        }
        
        $this->line('');
    }

    private function testVariantImages()
    {
        $this->info('TEST 3️⃣  VARIANT IMAGES');
        
        $total = ProductVariant::count();
        $withImage = ProductVariant::whereNotNull('image')->count();
        
        $this->line("Total variants: {$total}");
        $this->line("With image: {$withImage}");
        
        if ($withImage > 0) {
            $brokenCount = 0;
            $workingCount = 0;
            
            ProductVariant::whereNotNull('image')->chunk(10, function($variants) use (&$brokenCount, &$workingCount) {
                foreach ($variants as $variant) {
                    $exists = file_exists(storage_path('app/public/' . $variant->image));
                    if ($exists) {
                        $workingCount++;
                    } else {
                        $brokenCount++;
                    }
                }
            });
            
            $this->line("  - Working: {$workingCount} ✅");
            if ($brokenCount > 0) {
                $this->line("  - Broken: {$brokenCount} ❌");
            }
            
            // Show samples
            $this->line("\n📦 Sample variant images:");
            $variants = ProductVariant::whereNotNull('image')->limit(3)->get(['id', 'color', 'size', 'image']);
            
            foreach ($variants as $variant) {
                $exists = file_exists(storage_path('app/public/' . $variant->image));
                $status = $exists ? '✅' : '❌';
                $this->line("  {$status} {$variant->color}/{$variant->size}");
                $this->line("     Path: {$variant->image}");
            }
        }
        
        $this->line('');
    }

    private function testCustomDesignUploads()
    {
        $this->info('TEST 4️⃣  CUSTOM DESIGN UPLOADS');
        
        $total = CustomDesignUpload::count();
        $this->line("Total uploads: {$total}");
        
        if ($total > 0) {
            $brokenCount = 0;
            $workingCount = 0;
            
            CustomDesignUpload::chunk(20, function($uploads) use (&$brokenCount, &$workingCount) {
                foreach ($uploads as $upload) {
                    $exists = file_exists(storage_path('app/public/' . $upload->file_path));
                    if ($exists) {
                        $workingCount++;
                    } else {
                        $brokenCount++;
                    }
                }
            });
            
            $this->line("  - Working: {$workingCount} ✅");
            if ($brokenCount > 0) {
                $this->line("  - Broken: {$brokenCount} ❌");
            }
            
            // Show samples
            $this->line("\n📦 Sample custom design uploads:");
            $uploads = CustomDesignUpload::orderBy('created_at', 'desc')->limit(3)->get();
            
            foreach ($uploads as $upload) {
                $exists = file_exists(storage_path('app/public/' . $upload->file_path));
                $status = $exists ? '✅' : '❌';
                $this->line("  {$status} Order: {$upload->custom_design_order_id}, Section: {$upload->section_name}");
                $this->line("     Path: {$upload->file_path}");
                if ($exists) {
                    $size = filesize(storage_path('app/public/' . $upload->file_path));
                    $this->line("     Size: " . $this->formatBytes($size));
                }
            }
        }
        
        $this->line('');
    }

    private function testStorageDirectories()
    {
        $this->info('TEST 5️⃣  STORAGE DIRECTORIES');
        
        $dirs = [
            'Public Storage' => storage_path('app/public'),
            'Products' => storage_path('app/public/products'),
            'Variants' => storage_path('app/public/variants'),
            'Custom Designs' => storage_path('app/public/custom-designs'),
        ];
        
        foreach ($dirs as $name => $path) {
            $exists = is_dir($path);
            $writable = is_writable($path);
            $status = $exists ? '✅' : '❌';
            $write = $writable ? '✅' : '❌';
            
            $this->line("{$status} {$name}: {$path}");
            if ($exists) {
                $this->line("   Writable: {$write}");
                
                // Count files
                $files = @glob($path . '/*', GLOB_NOSORT);
                $fileCount = is_array($files) ? count($files) : 0;
                $this->line("   Files: {$fileCount}");
            }
        }
        
        $this->line('');
    }

    private function testImageUrlIntegrity()
    {
        $this->info('TEST 6️⃣  IMAGE URL INTEGRITY');
        
        $appUrl = config('app.url');
        $this->line("App URL: {$appUrl}");
        
        // Test product image URL format
        $product = Product::whereNotNull('image')->first();
        if ($product) {
            $this->line("\n✨ Sample product image URL generation:");
            $this->line("  Database value: {$product->image}");
            
            // Check if it's local path
            if (strpos($product->image, 'http') === 0) {
                $this->line("  Type: External URL");
                $this->line("  URL: {$product->image}");
            } else {
                $imageUrl = asset('storage/' . $product->image);
                $this->line("  Type: Local path");
                $this->line("  Generated URL: {$imageUrl}");
                
                // Try to access it
                $response = @get_headers($imageUrl);
                if ($response && strpos($response[0], '200') !== false) {
                    $this->line("  Status: ✅ Accessible");
                } else {
                    $this->line("  Status: ⚠️ May not be accessible from this IP");
                }
            }
        }
        
        // Test symlink
        $this->line("\n🔗 Storage Symlink Check:");
        $symlinkPath = public_path('storage');
        if (is_link($symlinkPath)) {
            $this->line("  Status: ✅ Symlink exists");
            $target = readlink($symlinkPath);
            $this->line("  Target: {$target}");
        } else {
            $this->warn("  Status: ❌ Symlink not found");
            $this->line("  Run: php artisan storage:link");
        }
        
        $this->line('');
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
