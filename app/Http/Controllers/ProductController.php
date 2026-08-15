<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function allProducts(Request $request)
    {
        $query = Product::with('variants')->active();

        // Filter promo (dengan diskon) - products with original_price > price
        if ($request->boolean('promo')) {
            $query->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
        }

        // Filter ready stock - products with stock > 0
        if ($request->boolean('ready')) {
            $query->where('stock', '>', 0);
        }

        // Filter custom - products that allow custom design
        if ($request->boolean('custom')) {
            $query->where('custom_design_allowed', true);
        }

        // Filter categories
        if ($request->filled('categories')) {
            $categories = is_array($request->categories) ? $request->categories : explode(',', $request->categories);
            $categories = array_values(array_filter($categories));
            if (!empty($categories)) {
                $query->whereIn('category', $categories);
            }
        }

        // Filter price range
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

        // Paginate results
        $products = $query->paginate(12)->appends($request->except('page'));
        
        // Calculate price range, total stock, and variant images from variants
        $productsData = collect($products->items())->map(function ($product) {
            if ($product->variants && $product->variants->count() > 0) {
                // Get price range
                $prices = $product->variants->pluck('price')->filter();
                $minPrice = $prices->min();
                $maxPrice = $prices->max();
                
                // Get total stock
                $totalStock = $product->variants->sum('stock');
                
                // Get variant images for carousel (use accessor for proper URL handling)
                $variantImages = $product->variants
                    ->filter(function($v) { return !empty($v->image); })
                    ->map(function($v) {
                        // Accessor automatically handles URL formatting
                        return $v->image;
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
                // Accessor automatically handles URL formatting
                $product->variant_images = $product->image ? [$product->image] : [];
                $product->variant_count = 0;
            }
            
            return $product;
        });
        
        // Update products collection
        $products->setCollection($productsData);

        // Prepare applied filters for view
        $appliedFilters = [
            'promo' => $request->boolean('promo'),
            'ready' => $request->boolean('ready'),
            'custom' => $request->boolean('custom'),
            'categories' => $request->filled('categories')
                ? (is_array($request->categories)
                    ? array_values(array_filter($request->categories))
                    : array_values(array_filter(explode(',', $request->categories))))
                : [],
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort,
        ];

        // Handle AJAX request
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
                'applied_filters' => $appliedFilters,
            ]);
        }

        return view('customer.all-product', compact('products', 'appliedFilters'));
    }

    public function detail(Request $request)
    {
        // Try to get product from database first
        if ($request->has('id')) {
            // Use find() instead of findOrFail() to avoid automatic 404
            $product = Product::with('variants')->where('is_active', true)->find($request->id);
            
            if ($product) {
                // Increment views
                $product->incrementViews();
                
                // Extract unique colors and sizes from variants
                $availableColors = [];
                $availableSizes = [];
                $variantsData = [];
                
                if ($product->variants && $product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        // Collect unique colors
                        if ($variant->color && !in_array($variant->color, $availableColors)) {
                            $availableColors[] = $variant->color;
                        }
                        
                        // Collect unique sizes
                        if ($variant->size && !in_array($variant->size, $availableSizes)) {
                            $availableSizes[] = $variant->size;
                        }
                        
                        // Format variant data
                        $variantsData[] = [
                            'id' => $variant->id,
                            'color' => $variant->color,
                            'size' => $variant->size,
                            'price' => $variant->price,
                            'original_price' => $variant->original_price,
                            'stock' => $variant->stock,
                            'image' => $variant->image ? image_url($variant->image) : null,
                        ];
                    }
                }
                
                // Use variant colors/sizes if available, otherwise fallback to product colors/sizes
                $colors = !empty($availableColors) ? $availableColors : ($product->colors ?: []);
                $sizes = !empty($availableSizes) ? $availableSizes : ($product->sizes ?: []);
                
                // Build gallery from variant images (each variant image represents a color/size combination)
                $gallery = [];
                if ($product->variants && $product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        if ($variant->image) {
                            $gallery[] = [
                                'url' => image_url($variant->image),
                                'color' => $variant->color,
                                'size' => $variant->size,
                            ];
                        }
                    }
                }
                
                // If no variant images, use product images
                if (empty($gallery)) {
                    if ($product->image) {
                        $gallery[] = ['url' => image_url($product->image), 'color' => null, 'size' => null];
                    }
                    if ($product->images && is_array($product->images)) {
                        foreach ($product->images as $img) {
                            $gallery[] = ['url' => image_url($img), 'color' => null, 'size' => null];
                        }
                    }
                }
                
                // Calculate price range from variants
                $priceMin = $product->price;
                $priceMax = $product->price;
                if (!empty($variantsData)) {
                    $prices = array_column($variantsData, 'price');
                    $priceMin = min($prices);
                    $priceMax = max($prices);
                }
                
                // Format for view
                $productData = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price, // Base price
                    'price_min' => $priceMin,
                    'price_max' => $priceMax,
                    'image' => $product->image ? image_url($product->image) : '',
                    'gallery' => $gallery,
                    'variants' => $variantsData,
                    'description' => $product->description ?: 'This product is crafted with superior quality and attention to detail.',
                    'colors' => $colors,
                    'sizes' => $sizes,
                    'stock' => $product->stock,
                    'category' => $product->category,
                    'subcategory' => $product->subcategory,
                    'custom_design_allowed' => (bool) ($product->custom_design_allowed ?? false),
                ];

                // Ambil rekomendasi produk (produk lain dari kategori yang sama)
                $recommendations = Product::where('category', $product->category)
                    ->where('id', '!=', $product->id)
                    ->where('is_active', true)
                    ->limit(4)
                    ->get()
                    ->map(function ($rec) {
                        return [
                            'id' => $rec->id,
                            'name' => $rec->name,
                            'price' => $rec->formatted_price,
                            'image' => $rec->image ? asset('storage/' . $rec->image) : '',
                            'custom_design_allowed' => (bool) $rec->custom_design_allowed,
                        ];
                    });

                return view('pages.product-detail', ['product' => $productData, 'recommendations' => $recommendations]);
            }
            // If product not found but has id, fall through to fallback
        }
        
        // Fallback: use query parameters (backward compatibility)
        $product = [
            'id' => $request->query('id', 1),
            'name' => $request->query('name', 'ONE LIFE GRAPHIC T-SHIRT'),
            'price' => $request->query('price', '70.000'),
            'image' => $request->query('image', 'https://i.pinimg.com/1200x/3e/6b/f5/3e6bf5378b6ae4d43263dfb626d37588.jpg'),
            'description' => 'This graphic t-shirt which is perfect for any occasion. Crafted from a soft and breathable fabric, it offers superior comfort and style.',
            'colors' => ['#4A5F4A', '#2C3E50', '#1A1A1A'],
            'sizes' => ['Small', 'Medium', 'Large', 'X-Large'],
            'stock' => 0,
            'category' => 'kaos',
            'custom_design_allowed' => false,
        ];

        // If query parameters exist (fallback for direct links)
        $recommendations = Product::where('is_active', true)
            ->limit(4)
            ->get()
            ->map(function ($rec) {
            return [
                'id' => $rec->id,
                'name' => $rec->name,
                'price' => $rec->formatted_price,
                'image' => $rec->image ? asset('storage/' . $rec->image) : '',
                'custom_design_allowed' => (bool) $rec->custom_design_allowed,
            ];
        });

        return view('pages.product-detail', compact('product', 'recommendations'));
    }
}
