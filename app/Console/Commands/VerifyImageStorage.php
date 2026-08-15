<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VerifyImageStorage extends Command
{
    protected $signature = 'images:verify';
    protected $description = 'Verify and check image storage structure and database integrity';

    public function handle()
    {
        $this->info('📸 IMAGE STORAGE VERIFICATION');
        $this->line('');
        
        // Check storage directories
        $this->checkStorageDirectories();
        $this->line('');
        
        // Check database records
        $this->checkDatabaseRecords();
        $this->line('');
        
        // Check file accessibility
        $this->checkFileAccessibility();
        $this->line('');
        
        $this->info('✅ Verification complete!');
    }

    private function checkStorageDirectories()
    {
        $this->info('1️⃣  CHECKING STORAGE DIRECTORIES:');
        
        $publicPath = storage_path('app/public');
        $this->line("   Public storage path: {$publicPath}");
        $this->line("   Exists: " . (is_dir($publicPath) ? '✅ YES' : '❌ NO'));
        
        // Check products directory
        $productsPath = storage_path('app/public/products');
        $this->line("\n   Products directory: {$productsPath}");
        if (is_dir($productsPath)) {
            $this->line("   ✅ EXISTS");
            $files = glob($productsPath . '/*');
            $this->line("   Files count: " . count($files));
            if (count($files) > 0) {
                $this->line("   Recent files:");
                $recent = array_slice(array_reverse($files), 0, 5);
                foreach ($recent as $file) {
                    $size = filesize($file);
                    $this->line("     - " . basename($file) . " (" . $this->formatBytes($size) . ")");
                }
            }
        } else {
            $this->line("   ❌ DOES NOT EXIST");
            $this->line("   Creating directory...");
            @mkdir($productsPath, 0755, true);
            $this->line(is_dir($productsPath) ? "   ✅ Created successfully" : "   ❌ Failed to create");
        }
        
        // Check custom designs directory
        $customPath = storage_path('app/public/custom-designs');
        $this->line("\n   Custom designs directory: {$customPath}");
        if (is_dir($customPath)) {
            $this->line("   ✅ EXISTS");
            $orders = glob($customPath . '/*', GLOB_ONLYDIR);
            $this->line("   Order folders: " . count($orders));
            if (count($orders) > 0) {
                $recent = array_slice(array_reverse($orders), 0, 3);
                foreach ($recent as $order) {
                    $files = glob($order . '/*');
                    $this->line("     - " . basename($order) . ": " . count($files) . " files");
                }
            }
        } else {
            $this->line("   ❌ DOES NOT EXIST");
            $this->line("   Creating directory...");
            @mkdir($customPath, 0755, true);
            $this->line(is_dir($customPath) ? "   ✅ Created successfully" : "   ❌ Failed to create");
        }
        
        // Check variants directory
        $variantsPath = storage_path('app/public/variants');
        $this->line("\n   Variants directory: {$variantsPath}");
        if (is_dir($variantsPath)) {
            $this->line("   ✅ EXISTS");
            $files = glob($variantsPath . '/*');
            $this->line("   Files count: " . count($files));
        } else {
            $this->line("   ❌ DOES NOT EXIST");
        }
    }

    private function checkDatabaseRecords()
    {
        $this->info('2️⃣  CHECKING DATABASE RECORDS:');
        
        // Check products with images
        $totalProducts = \App\Models\Product::count();
        $withImage = \App\Models\Product::whereNotNull('image')->count();
        $withImages = \App\Models\Product::whereNotNull('images')->count();
        
        $this->line("\n   Products:");
        $this->line("   - Total: {$totalProducts}");
        $this->line("   - With 'image' field: {$withImage}");
        $this->line("   - With 'images' field: {$withImages}");
        
        // Sample products
        if ($withImage > 0) {
            $this->line("\n   Sample products with image:");
            $samples = \App\Models\Product::whereNotNull('image')->limit(5)->get(['id', 'name', 'image']);
            foreach ($samples as $product) {
                $exists = file_exists(storage_path('app/public/' . $product->image));
                $status = $exists ? '✅' : '❌';
                $this->line("   {$status} ID: {$product->id} | Name: {$product->name}");
                $this->line("       Path: {$product->image}");
            }
        }
        
        // Check variants with images
        $variantsWithImage = \App\Models\ProductVariant::whereNotNull('image')->count();
        $this->line("\n   Product Variants:");
        $this->line("   - With image: {$variantsWithImage}");
        
        if ($variantsWithImage > 0) {
            $this->line("   Sample variants with image:");
            $samples = \App\Models\ProductVariant::whereNotNull('image')->limit(3)->get(['id', 'product_id', 'color', 'size', 'image']);
            foreach ($samples as $variant) {
                $exists = file_exists(storage_path('app/public/' . $variant->image));
                $status = $exists ? '✅' : '❌';
                $this->line("   {$status} ID: {$variant->id} | {$variant->color}/{$variant->size}");
                $this->line("       Path: {$variant->image}");
            }
        }
        
        // Check custom design uploads
        $totalUploads = \App\Models\CustomDesignUpload::count();
        $this->line("\n   Custom Design Uploads:");
        $this->line("   - Total: {$totalUploads}");
        
        if ($totalUploads > 0) {
            $this->line("   Sample uploads:");
            $samples = \App\Models\CustomDesignUpload::orderBy('created_at', 'desc')->limit(5)->get(['id', 'custom_design_order_id', 'section_name', 'file_path']);
            foreach ($samples as $upload) {
                $exists = file_exists(storage_path('app/public/' . $upload->file_path));
                $status = $exists ? '✅' : '❌';
                $this->line("   {$status} ID: {$upload->id} | Order: {$upload->custom_design_order_id} | Section: {$upload->section_name}");
                $this->line("       Path: {$upload->file_path}");
            }
        }
    }

    private function checkFileAccessibility()
    {
        $this->info('3️⃣  CHECKING FILE ACCESSIBILITY:');
        
        $publicPath = storage_path('app/public');
        $permissions = decoct(fileperms($publicPath));
        $this->line("\n   Storage permissions: {$permissions}");
        
        $isWritable = is_writable($publicPath);
        $this->line("   Writable: " . ($isWritable ? '✅ YES' : '❌ NO'));
        
        if (!$isWritable) {
            $this->warn("\n   ⚠️  Storage is not writable. Run:");
            $this->line("   chmod -R 777 " . $publicPath);
        }
        
        // Check if there are any broken image references in database
        $this->line("\n   Checking for broken image references:");
        
        $brokenImages = \App\Models\Product::whereNotNull('image')
            ->get()
            ->filter(function($product) {
                return !file_exists(storage_path('app/public/' . $product->image));
            });
        
        if ($brokenImages->count() > 0) {
            $this->warn("   Found {$brokenImages->count()} broken product images:");
            foreach ($brokenImages as $product) {
                $this->line("   - {$product->name}: {$product->image}");
            }
        } else {
            $this->info("   ✅ All product images are accessible");
        }
        
        $brokenVariants = \App\Models\ProductVariant::whereNotNull('image')
            ->get()
            ->filter(function($variant) {
                return !file_exists(storage_path('app/public/' . $variant->image));
            });
        
        if ($brokenVariants->count() > 0) {
            $this->warn("   Found {$brokenVariants->count()} broken variant images:");
        } else {
            $this->info("   ✅ All variant images are accessible");
        }
        
        $brokenUploads = \App\Models\CustomDesignUpload::whereNotNull('file_path')
            ->get()
            ->filter(function($upload) {
                return !file_exists(storage_path('app/public/' . $upload->file_path));
            });
        
        if ($brokenUploads->count() > 0) {
            $this->warn("   Found {$brokenUploads->count()} broken custom design uploads:");
        } else {
            $this->info("   ✅ All custom design uploads are accessible");
        }
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
