<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryManagementController extends Controller
{
    /**
     * Get all subcategories for category "lainnya"
     */
    public function index(Request $request)
    {
        try {
            $subcategories = Subcategory::where('category', 'lainnya')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories->pluck('name')->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat sub kategori'
            ], 500);
        }
    }

    /**
     * Store a new subcategory
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $name = trim($request->name);
            $slug = Str::slug($name);

            // Check if slug already exists
            if (Subcategory::where('slug', $slug)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sub kategori sudah ada'
                ], 422);
            }

            $subcategory = Subcategory::create([
                'name' => $name,
                'slug' => $slug,
                'category' => 'lainnya',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sub kategori berhasil ditambahkan',
                'data' => $subcategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan sub kategori'
            ], 500);
        }
    }

    /**
     * Delete a subcategory
     */
    public function destroy($slug)
    {
        try {
            $subcategory = Subcategory::where('slug', $slug)->first();

            if (!$subcategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sub kategori tidak ditemukan'
                ], 404);
            }

            $subcategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sub kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sub kategori'
            ], 500);
        }
    }
}
