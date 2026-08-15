<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;

class AnalyzeImageIssues extends Command
{
    protected $signature = 'images:analyze {--fix : Automatically fix issues} {--deep : Deep analysis including file checks}';
    protected $description = 'Analyze image storage issues and suggest fixes';

    public function handle()
    {
        $this->line('🔍 ANALYZING IMAGE ISSUES...\n');
        $this->line('Environment: ' . app()->environment());
        $this->line('Storage Path: ' . storage_path('app/public'));
        $this->line('Public Path: ' . public_path());
        $this->line('');

        // 1. Check storage directories
        $this->checkStorageDirectories();

        // 2. Check database image values
        $this->checkDatabaseImages();

        // 3. Check symlink
        $this->checkSymlink();

        // 4. Deep analysis if requested
        if ($this->option('deep')) {
            $this->deepAnalysis();
        }

        // 5. Suggest fixes
        $this->suggestFixes();

        if ($this->option('fix')) {
            $this->performFixes();
        }
    }

    private function deepAnalysis()
    {
        $this->line('🔬 DEEP ANALYSIS:\n');

        // Check file accessibility
        $this->line('📊 Checking file accessibility:');
        $directories = [
            'products' => storage_path('app/public/products'),
            'variants' => storage_path('app/public/variants'),
        ];

        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $files = array_diff(scandir($path), ['.', '..']);
                if (count($files) > 0) {
                    $testFile = $path . '/' . reset($files);
                    if (is_readable($testFile)) {
                        $this->line("  ✅ {$name}/: files are readable");
                    } else {
                        $this->line("  ❌ {$name}/: permission denied");
                    }
                    
                    // Check file via symlink
                    $symlinkPath = public_path('storage/' . $name . '/' . basename($testFile));
                    if (file_exists($symlinkPath)) {
                        $this->line("  ✅ {$name}/: accessible via symlink");
                    } else {
                        $this->line("  ❌ {$name}/: NOT accessible via symlink");
                    }
                }
            }
        }

        // Check database vs filesystem mismatch
        $this->line('\n🔄 Checking database vs filesystem mismatch:');
        $missingFiles = 0;
        
        ProductVariant::whereNotNull('image')->limit(10)->get()->each(function($variant) use (&$missingFiles) {
            $filePath = storage_path('app/public/' . $variant->image);
            if (!file_exists($filePath)) {
                $this->line("  ❌ Missing: variants/{$variant->image}");
                $missingFiles++;
            }
        });

        if ($missingFiles === 0) {
            $this->line("  ✅ All checked variant files exist in filesystem");
        } else {
            $this->line("  ⚠️  Found {$missingFiles} missing files");
        }

        $this->line('');
    }

    private function checkStorageDirectories()
    {
        $this->line('📁 CHECKING STORAGE DIRECTORIES:');

        $directories = [
            'products' => storage_path('app/public/products'),
            'variants' => storage_path('app/public/variants'),
            'custom-designs' => storage_path('app/public/custom-designs'),
        ];

        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $count = count(array_diff(scandir($path), ['.', '..']));
                $status = $count > 0 ? '✅' : '⚠️';
                $this->line("  {$status} {$name}/: {$count} files");
            } else {
                $this->line("  ❌ {$name}/: DIRECTORY NOT FOUND");
            }
        }
        $this->line('');
    }

    private function checkDatabaseImages()
    {
        $this->line('🗄️  CHECKING DATABASE:');

        // Products with NULL image
        $nullImages = Product::whereNull('image')->count();
        $withImages = Product::whereNotNull('image')->count();
        
        $this->line("  Products:");
        $this->line("    ✅ With image: {$withImages}");
        $this->line("    ❌ NULL image: {$nullImages}");

        // Show products with NULL images
        if ($nullImages > 0) {
            $this->line("\n  Products with NULL images:");
            Product::whereNull('image')->select('id', 'name')->limit(5)->get()->each(function($p) {
                $this->line("    - ID {$p->id}: {$p->name}");
            });
        }

        // Variants analysis
        $variantNull = ProductVariant::whereNull('image')->count();
        $variantWith = ProductVariant::whereNotNull('image')->count();
        
        $this->line("\n  Product Variants:");
        $this->line("    ✅ With image: {$variantWith}");
        $this->line("    ❌ NULL image: {$variantNull}");
        
        $this->line('');
    }

    private function checkSymlink()
    {
        $this->line('🔗 CHECKING SYMLINK:');

        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (is_link($linkPath)) {
            $target = readlink($linkPath);
            $this->line("  ✅ Symlink exists: {$linkPath}");
            $this->line("  ✅ Points to: {$target}");
            
            if (is_dir($targetPath)) {
                $this->line("  ✅ Target directory exists");
            } else {
                $this->line("  ❌ Target directory NOT FOUND");
            }
        } elseif (is_dir($linkPath)) {
            $this->line("  ⚠️  {$linkPath} is a DIRECTORY (not symlink)");
            $this->line("      This might cause issues on production");
        } else {
            $this->line("  ❌ Symlink MISSING: {$linkPath}");
            $this->line("      Run: php artisan storage:link");
        }
        
        $this->line('');
    }

    private function suggestFixes()
    {
        $this->line('💡 SUGGESTED FIXES:\n');
        
        $nullCount = Product::whereNull('image')->count();
        $symlinkOk = is_link(public_path('storage'));
        
        if ($nullCount > 0) {
            $this->line("1️⃣  Products with NULL images ({$nullCount}):");
            $this->line("    Run: php artisan images:analyze --fix\n");
        }

        if (!$symlinkOk) {
            $this->line("2️⃣  Symlink issue:");
            $this->line("    SSH: ssh u157843933@sablontopilampung.com");
            $this->line("    Then run:");
            $this->line("      rm -rf public/storage");
            $this->line("      php artisan storage:link\n");
        }

        $this->line("3️⃣  After fixes, clear cache:");
        $this->line("    php artisan optimize:clear");
        $this->line("    php artisan config:cache\n");

        $this->line("4️⃣  Browser:");
        $this->line("    Press Ctrl+Shift+Delete to clear browser cache");
        $this->line("    Refresh halaman\n");
    }

    private function performFixes()
    {
        $this->line('🔧 PERFORMING AUTO-FIXES...\n');

        // Fix 1: Auto-assign variant images to products
        $this->line('Fix 1: Auto-assign variant images to products');
        
        $products = Product::whereNull('image')->get();
        $count = 0;

        foreach ($products as $product) {
            $variantImage = ProductVariant::where('product_id', $product->id)
                ->whereNotNull('image')
                ->first();

            if ($variantImage) {
                $product->update(['image' => $variantImage->image]);
                $count++;
                $this->line("  ✅ Product ID {$product->id}: assigned image from variant");
            }
        }

        $this->line("  Total fixed: {$count}\n");

        // Fix 2: Create symlink if missing
        if (!is_link(public_path('storage'))) {
            $this->line('Fix 2: Creating symlink');
            try {
                // Remove existing file/directory if exists
                if (file_exists(public_path('storage'))) {
                    if (is_link(public_path('storage'))) {
                        unlink(public_path('storage'));
                    } else {
                        rmdir(public_path('storage'));
                    }
                }
                
                // Create symlink
                \Artisan::call('storage:link');
                $this->line("  ✅ Symlink created successfully\n");
            } catch (\Exception $e) {
                $this->line("  ❌ Failed to create symlink: {$e->getMessage()}\n");
            }
        }

        $this->line('✨ Auto-fixes completed!');
    }
}
