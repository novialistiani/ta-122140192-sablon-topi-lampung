<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\PlaceholderImageService;
use Illuminate\Support\Str;

class FixSeededImages extends Command
{
    protected $signature = 'images:fix-seeded {--download : Download images from URLs} {--placeholder : Use placeholder images instead}';
    protected $description = 'Fix seeded product images - either download from URLs or use placeholders';

    public function handle()
    {
        $this->info('🖼️  FIXING SEEDED PRODUCT IMAGES');
        $this->line('');
        
        // Get all products with external URLs
        $products = Product::whereNotNull('image')
            ->where(function($q) {
                $q->where('image', 'like', 'https://%')
                  ->orWhere('image', 'like', 'http://%');
            })
            ->get();
        
        if ($products->isEmpty()) {
            $this->info('✅ No external image URLs found. All images are local!');
            return;
        }
        
        $this->warn("Found {$products->count()} products with external image URLs");
        $this->line('');
        
        $download = $this->option('download');
        $placeholder = $this->option('placeholder');
        
        if (!$download && !$placeholder) {
            $this->error('Please specify --download or --placeholder option');
            $this->info('Example: php artisan images:fix-seeded --download');
            return;
        }
        
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();
        
        foreach ($products as $product) {
            try {
                $filename = Str::slug($product->name) . '-' . Str::random(10);
                $newPath = null;
                
                if ($download) {
                    $newPath = PlaceholderImageService::downloadAndSave(
                        $product->image,
                        $filename,
                        'products'
                    );
                } elseif ($placeholder) {
                    $newPath = PlaceholderImageService::generateAndSave(
                        $filename,
                        substr($product->name, 0, 20),
                        'products'
                    );
                }
                
                if ($newPath) {
                    $product->update(['image' => $newPath]);
                } else {
                    $this->warn("\n⚠️  Could not process image for: {$product->name}");
                }
            } catch (\Exception $e) {
                $this->warn("\n❌ Error processing {$product->name}: {$e->getMessage()}");
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->line('');
        $this->line('');
        $this->info('✅ Fixed seeded product images!');
    }
}
