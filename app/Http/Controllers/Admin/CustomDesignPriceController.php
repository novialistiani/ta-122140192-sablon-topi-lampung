<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomDesignPrice;
use Illuminate\Http\Request;

class CustomDesignPriceController extends Controller
{
    /**
     * Display the custom design price management page
     */
    public function index()
    {
        $currentAdmin = auth('admin')->user();
        $uploadSections = CustomDesignPrice::where('type', 'upload_section')->get();
        $cuttingTypes = CustomDesignPrice::where('type', 'cutting_type')->get();
        
        return view('admin.custom-design-prices', compact('currentAdmin', 'uploadSections', 'cuttingTypes'));
    }

    /**
     * Initialize default prices (run once or when reset needed)
     */
    public function initializeDefaults()
    {
        $uploadSections = [
            ['code' => 'A', 'name' => 'A (Dada depan horizontal, uk. A4)', 'price' => 50000],
            ['code' => 'B', 'name' => 'B (Gambar kantong kiri, uk. 10x10 cm)', 'price' => 25000],
            ['code' => 'C', 'name' => 'C (Dada siku kanan, uk. 10x10 cm)', 'price' => 25000],
            ['code' => 'D', 'name' => 'D (Dada depan vertikal, uk. A4)', 'price' => 50000],
            ['code' => 'E', 'name' => 'E (Punggung belakang vertikal, uk. A4)', 'price' => 50000],
            ['code' => 'F', 'name' => 'F (Punggung siku kanan, uk. 10x10 cm)', 'price' => 25000],
            ['code' => 'G', 'name' => 'G (Dada depan horizontal, uk. A3)', 'price' => 75000],
            ['code' => 'H', 'name' => 'H (Dada depan ver sisi, uk. A3)', 'price' => 75000],
            ['code' => 'I', 'name' => 'I (Punggung belakang horizontal, uk. A4)', 'price' => 50000],
            ['code' => 'J', 'name' => 'J (Punggung belakang horizontal, uk. A3)', 'price' => 75000],
        ];

        foreach ($uploadSections as $section) {
            CustomDesignPrice::updateOrCreate(
                ['type' => 'upload_section', 'code' => $section['code']],
                ['name' => $section['name'], 'price' => $section['price'], 'is_active' => true]
            );
        }

        $cuttingTypes = [
            ['code' => 'cutting-pvc-flex', 'name' => 'Cutting PVC Flex', 'price' => 30000],
            ['code' => 'printable', 'name' => 'Printable', 'price' => 40000],
        ];

        foreach ($cuttingTypes as $cutting) {
            CustomDesignPrice::updateOrCreate(
                ['type' => 'cutting_type', 'code' => $cutting['code']],
                ['name' => $cutting['name'], 'price' => $cutting['price'], 'is_active' => true]
            );
        }

        return redirect()->route('admin.custom-design-prices')->with('success', 'Default prices initialized successfully!');
    }

    /**
     * Update price
     */
    public function updatePrice(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0'
        ]);

        $item = CustomDesignPrice::findOrFail($id);
        $item->price = $request->price;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Harga berhasil diupdate',
            'data' => $item
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        $item = CustomDesignPrice::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => $item
        ]);
    }

    /**
     * Get all prices for customer (API)
     */
    public function getPrices()
    {
        $uploadSections = CustomDesignPrice::uploadSections()->get();
        $cuttingTypes = CustomDesignPrice::cuttingTypes()->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'upload_sections' => $uploadSections,
                'cutting_types' => $cuttingTypes
            ]
        ]);
    }

    /**
     * Get custom design prices for specific product (for customer page)
     * Only returns prices that are enabled for this product
     */
    public function getProductPrices($productId)
    {
        try {
            $product = \App\Models\Product::findOrFail($productId);
            
            // Check if product allows custom design
            if (!$product->custom_design_allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Custom design not allowed for this product'
                ], 400);
            }
            
            // Get product-specific custom design prices from pivot table
            $productCustomPrices = $product->customDesignPrices()
                ->where('product_custom_design_prices.is_active', true)
                ->get();
            
            // Separate into upload sections and cutting types
            $uploadSections = $productCustomPrices->where('type', 'upload_section')->values();
            $cuttingTypes = $productCustomPrices->where('type', 'cutting_type')->values();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'upload_sections' => $uploadSections,
                    'cutting_types' => $cuttingTypes
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }
}
