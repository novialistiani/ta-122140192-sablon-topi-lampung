<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ColorManagementController extends Controller
{
    /**
     * Get saved colors
     */
    public function index()
    {
        try {
            $colors = Cache::get('admin_saved_colors', []);
            
            return response()->json([
                'success' => true,
                'data' => $colors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load colors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save a new color
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid color format. Use hex format like #FF0000',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $color = strtoupper($request->color);
            $colors = Cache::get('admin_saved_colors', []);
            
            if (!in_array($color, $colors)) {
                $colors[] = $color;
                Cache::put('admin_saved_colors', $colors, now()->addDays(30));
            }

            return response()->json([
                'success' => true,
                'message' => 'Color saved successfully',
                'data' => $colors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save color: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a color
     */
    public function destroy(Request $request, $color)
    {
        try {
            $colors = Cache::get('admin_saved_colors', []);
            $colors = array_filter($colors, function($c) use ($color) {
                return $c !== $color;
            });
            
            Cache::put('admin_saved_colors', array_values($colors), now()->addDays(30));

            return response()->json([
                'success' => true,
                'message' => 'Color deleted successfully',
                'data' => array_values($colors)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete color: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all colors
     */
    public function clear()
    {
        try {
            Cache::forget('admin_saved_colors');

            return response()->json([
                'success' => true,
                'message' => 'All colors cleared successfully',
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear colors: ' . $e->getMessage()
            ], 500);
        }
    }
}