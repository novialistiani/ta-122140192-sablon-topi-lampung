<?php

if (!function_exists('image_url')) {
    /**
     * Generate proper image URL from storage path or full URL
     * Handles Hostinger LiteSpeed server that requires /public/storage/ instead of /storage/
     * 
     * @param string|null $path - Storage path or full URL
     * @param string $placeholder - Placeholder URL if path is empty
     * @return string - Full image URL
     */
    function image_url(?string $path, string $placeholder = null): string
    {
        // Return placeholder if path is empty
        if (!$path) {
            return $placeholder ?? 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22400%22 height=%22400%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2224%22 fill=%22%23999%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';
        }
        
        // If it's already a full URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        // For Hostinger production: use /public/storage instead of /storage
        $cleanPath = ltrim($path, '/');
        if (app()->environment('production') && config('app.url') && strpos(config('app.url'), 'sablontopilampung.com') !== false) {
            // Hostinger LiteSpeed requires /public/storage path
            return asset('public/storage/' . $cleanPath);
        }
        
        // For local/other environments: use standard /storage prefix
        return asset('storage/' . $cleanPath);
    }
}

if (!function_exists('image_exists')) {
    /**
     * Check if image file exists
     * 
     * @param string|null $path - Storage path (without /storage/ prefix)
     * @return bool
     */
    function image_exists(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        
        // Skip check for external URLs
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true; // Assume external URLs are valid
        }
        
        return \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }
}

if (!function_exists('image_safe')) {
    /**
     * Get safe image URL with fallback to placeholder
     * Handles Hostinger LiteSpeed server that requires /public/storage/ instead of /storage/
     * 
     * @param string|null $path - Storage path or full URL
     * @param string $placeholder - Fallback placeholder
     * @return string - Safe image URL
     */
    function image_safe(?string $path, string $placeholder = null): string
    {
        // Return placeholder if path is empty
        if (!$path) {
            return $placeholder ?? route('images.serve') . '?path=' . urlencode('placeholder.svg');
        }
        
        // If it's already a full URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        // Check if file exists
        if (image_exists($path)) {
            // For Hostinger production: use /public/storage instead of /storage
            $cleanPath = ltrim($path, '/');
            if (app()->environment('production') && config('app.url') && strpos(config('app.url'), 'sablontopilampung.com') !== false) {
                return asset('public/storage/' . $cleanPath);
            }
            return asset('storage/' . $cleanPath);
        }
        
        // Return placeholder if file doesn't exist
        return $placeholder ?? route('images.serve') . '?path=' . urlencode('placeholder.svg');
    }
}
