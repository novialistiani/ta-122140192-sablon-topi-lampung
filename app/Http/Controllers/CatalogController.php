<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CatalogController extends Controller
{
    // Categories mapping
    private $categories = [
        'jersey' => 'Jersey',
        'topi' => 'Topi',
        'kaos' => 'Kaos',
        'polo' => 'Polo',
        'celana' => 'Celana',
        'jaket' => 'Jaket',
        'lainnya' => 'Lainnya'
    ];

    public function index(Request $request, $category)
    {
        // Validate category
        if (!array_key_exists($category, $this->categories)) {
            abort(404);
        }

        $categoryName = $this->categories[$category];
        
        // Start building query - Get ALL active products (not limited by stock)
        $query = Product::with('variants')->active()->category($category);

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply quick filters
        if ($request->boolean('promo')) {
            $query->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
        }

        if ($request->boolean('ready')) {
            $query->where('stock', '>', 0);
        }

        if ($request->boolean('custom')) {
            $query->where('custom_design_allowed', true);
        }

        // Apply subcategories filter (multiple)
        if ($request->filled('subcategories')) {
            $subcategories = is_array($request->subcategories) ? $request->subcategories : explode(',', $request->subcategories);
            $subcategories = array_values(array_filter($subcategories));
            if (!empty($subcategories)) {
                $query->whereIn('subcategory', $subcategories);
            }
        }

        // Apply color filter
        if ($request->filled('colors')) {
            $colors = is_array($request->colors) ? $request->colors : explode(',', $request->colors);
            $query->filterByColors($colors);
        }

        // Apply size filter
        if ($request->filled('sizes')) {
            $sizes = is_array($request->sizes) ? $request->sizes : explode(',', $request->sizes);
            $query->filterBySizes($sizes);
        }

        // Apply price range filter
        $minPrice = $request->filled('min_price') ? (int) preg_replace('/[^\d]/', '', $request->min_price) : null;
        $maxPrice = $request->filled('max_price') ? (int) preg_replace('/[^\d]/', '', $request->max_price) : null;

        if (!is_null($minPrice) && !is_null($maxPrice) && $maxPrice >= $minPrice) {
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        } elseif (!is_null($minPrice)) {
            $query->where('price', '>=', $minPrice);
        } elseif (!is_null($maxPrice)) {
            $query->where('price', '<=', $maxPrice);
        }

        // Apply sorting
        $sort = $request->get('sort', 'most_popular');
        $query->sortBy($sort);

        // Get total count before pagination
        $totalProducts = $query->count();

        // Paginate results
        $perPage = $request->get('per_page', 9);
        $products = $query->paginate($perPage)->appends($request->except('page'));
        
        // Calculate price range, total stock, and variant images from variants
        $productsData = collect($products->items())->map(function ($product) {
            if ($product->variants && $product->variants->count() > 0) {
                // Get price range
                $prices = $product->variants->pluck('price')->filter();
                $minPrice = $prices->min();
                $maxPrice = $prices->max();
                
                // Get total stock
                $totalStock = $product->variants->sum('stock');
                
                // Get variant images for carousel
                $variantImages = $product->variants
                    ->filter(function($v) { return !empty($v->image); })
                    ->map(function($v) {
                        return image_url($v->image);
                    })
                    ->values()
                    ->toArray();
                
                // Add computed fields
                $product->price_min = $minPrice;
                $product->price_max = $maxPrice;
                $product->price_range = $minPrice == $maxPrice 
                    ? "Rp " . number_format($minPrice, 0, ',', '.')
                    : "Rp " . number_format($minPrice, 0, ',', '.') . " - Rp " . number_format($maxPrice, 0, ',', '.');
                $product->total_stock = $totalStock;
                $product->variant_images = $variantImages;
                $product->variant_count = $product->variants->count();
            } else {
                // Fallback to product's own price and stock
                $product->price_min = $product->price;
                $product->price_max = $product->price;
                $product->price_range = "Rp " . number_format($product->price, 0, ',', '.');
                $product->total_stock = $product->stock;
                $product->variant_images = $product->image ? [image_url($product->image)] : [];
                $product->variant_count = 0;
            }
            
            return $product;
        });
        
        // Update products collection
        $products->setCollection($productsData);

        // Get available filters for sidebar
        $availableColors = $this->getAvailableColors($category);
        $availableSizes = $this->getAvailableSizes($category);
        $availableSubcategories = $this->getAvailableSubcategories($category);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'products' => $productsData->toArray(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'total_products' => $totalProducts,
            ]);
        }

        return view('pages.catalog', [
            'category' => $category,
            'categoryName' => $categoryName,
            'products' => $products,
            'totalProducts' => $totalProducts,
            'availableColors' => $availableColors,
            'availableSizes' => $availableSizes,
            'availableSubcategories' => $availableSubcategories,
            'currentFilters' => [
                'search' => $request->search,
                'promo' => $request->boolean('promo'),
                'ready' => $request->boolean('ready'),
                'custom' => $request->boolean('custom'),
                'subcategories' => $request->has('subcategories')
                    ? (is_array($request->subcategories)
                        ? array_values(array_filter($request->subcategories))
                        : array_values(array_filter(explode(',', $request->subcategories))))
                    : [],
                'colors' => $request->has('colors')
                    ? (is_array($request->colors)
                        ? array_values(array_filter($request->colors))
                        : array_values(array_filter(explode(',', $request->colors))))
                    : [],
                'sizes' => $request->has('sizes')
                    ? (is_array($request->sizes)
                        ? array_values(array_filter($request->sizes))
                        : array_values(array_filter(explode(',', $request->sizes))))
                    : [],
                'sort' => $sort,
            ],
        ]);
    }

    private function getAvailableColors($category)
    {
        $products = Product::active()->category($category)->get();
        $colors = [];
        
        foreach ($products as $product) {
            if ($product->colors) {
                $colors = array_merge($colors, $product->colors);
            }
        }
        
        return array_unique($colors);
    }

    private function getAvailableSizes($category)
    {
        $products = Product::active()->category($category)->get();
        $sizes = [];
        
        foreach ($products as $product) {
            if ($product->sizes) {
                $sizes = array_merge($sizes, $product->sizes);
            }
        }
        
        return array_unique($sizes);
    }

    private function getAvailableSubcategories($category)
    {
        return Product::active()
            ->category($category)
            ->whereNotNull('subcategory')
            ->distinct()
            ->pluck('subcategory')
            ->toArray();
    }
}
