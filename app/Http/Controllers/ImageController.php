<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    /**
     * Serve image from storage with proper error handling
     * Usage: /api/image/{path} where path is encoded storage path
     */
    public function serve(Request $request, $path = null)
    {
        try {
            // Get path from query or parameter
            $imagePath = $path ?? $request->get('path');
            
            if (!$imagePath) {
                return response()->json(['error' => 'Image path not provided'], 400);
            }
            
            // Decode path if it's URL encoded
            $imagePath = urldecode($imagePath);
            
            // Security: Prevent directory traversal
            if (strpos($imagePath, '..') !== false) {
                return response()->json(['error' => 'Invalid path'], 400);
            }
            
            // Check if file exists in public storage
            if (!Storage::disk('public')->exists($imagePath)) {
                \Log::warning("Image not found: {$imagePath}");
                return response()->json(['error' => 'Image not found'], 404);
            }
            
            // Get file content
            $file = Storage::disk('public')->get($imagePath);
            $mimeType = Storage::disk('public')->mimeType($imagePath);
            
            return Response::make($file, 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400', // Cache for 24 hours
                'ETag' => hash('sha256', $file),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Image serving error: ' . $e->getMessage());
            return response()->json(['error' => 'Error serving image'], 500);
        }
    }

    /**
     * Get image URL for a given storage path
     * This returns proper full URL for the image
     */
    public function getUrl($path = null)
    {
        try {
            $imagePath = $path ?? request()->get('path');
            
            if (!$imagePath) {
                return response()->json(['error' => 'Image path not provided'], 400);
            }
            
            // Decode if encoded
            $imagePath = urldecode($imagePath);
            
            // Check if it's already a full URL
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return response()->json(['url' => $imagePath]);
            }
            
            // Check if file exists
            if (!Storage::disk('public')->exists($imagePath)) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            
            // Return full URL
            $url = asset('storage/' . $imagePath);
            
            return response()->json([
                'url' => $url,
                'path' => $imagePath,
                'exists' => true,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting image URL: ' . $e->getMessage());
            return response()->json(['error' => 'Error processing request'], 500);
        }
    }

    /**
     * Validate image path and check if it exists
     */
    public function validate(Request $request)
    {
        try {
            $imagePath = $request->get('path');
            
            if (!$imagePath) {
                return response()->json(['error' => 'Path not provided'], 400);
            }
            
            $imagePath = urldecode($imagePath);
            
            // Check if it's a full URL
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'valid' => true,
                    'type' => 'external',
                    'url' => $imagePath,
                ]);
            }
            
            // Check if local file exists
            $exists = Storage::disk('public')->exists($imagePath);
            
            return response()->json([
                'valid' => $exists,
                'type' => 'local',
                'path' => $imagePath,
                'url' => $exists ? asset('storage/' . $imagePath) : null,
                'message' => $exists ? 'File exists' : 'File not found',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
