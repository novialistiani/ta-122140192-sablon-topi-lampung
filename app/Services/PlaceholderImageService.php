<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\GdDriver;

class PlaceholderImageService
{
    /**
     * Generate a simple placeholder image and save it
     * @param string $filename - Name of the file to save
     * @param string $text - Text to display on image
     * @param string $directory - Storage directory (default: 'products')
     * @return string - Path to saved image
     */
    public static function generateAndSave(string $filename, string $text = '', string $directory = 'products'): string
    {
        try {
            $manager = new ImageManager(new GdDriver());
            
            // Create a simple colored image (400x400)
            $image = $manager->create(400, 400)
                ->fill('#f0f0f0');
            
            // Add text in the middle if provided
            if ($text) {
                $image->text($text, 200, 200, function($font) {
                    $font->size(24);
                    $font->color('#666666');
                    $font->align('center');
                    $font->valign('middle');
                });
            }
            
            // Save the image
            $path = $directory . '/' . $filename . '.png';
            Storage::disk('public')->put($path, (string) $image->toPng());
            
            return $path;
        } catch (\Exception $e) {
            \Log::error('Failed to generate placeholder: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Download and save image from URL
     * @param string $url - Remote image URL
     * @param string $filename - Local filename
     * @param string $directory - Storage directory
     * @return string|null - Path to saved image or null if failed
     */
    public static function downloadAndSave(string $url, string $filename, string $directory = 'products'): ?string
    {
        try {
            // Download image
            $imageContent = @file_get_contents($url);
            if (!$imageContent) {
                return null;
            }
            
            // Get extension from URL
            $urlPath = parse_url($url, PHP_URL_PATH);
            $extension = pathinfo($urlPath, PATHINFO_EXTENSION) ?: 'jpg';
            
            // Save to storage
            $path = $directory . '/' . $filename . '.' . $extension;
            Storage::disk('public')->put($path, $imageContent);
            
            \Log::info("Downloaded and saved image: {$url} to {$path}");
            
            return $path;
        } catch (\Exception $e) {
            \Log::error('Failed to download image: ' . $e->getMessage());
            return null;
        }
    }
}
