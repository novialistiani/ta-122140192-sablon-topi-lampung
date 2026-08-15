<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductManagementController extends Controller
{
    /**
     * Display the product management page
     */
    public function index()
    {
        return view('admin.management-product');
    }

    /**
     * Display the admin all products page
     */
    public function allProducts()
    {
        return view('admin.all-products');
    }

    /**
     * Display the product detail page with order filters
     */
    public function productDetail($id)
    {
        $product = Product::with('variants')->findOrFail($id);
        
        // Query all orders containing this product
        // Orders items column contains JSON array with product IDs
        $orders = \App\Models\Order::whereJsonContains('items', function($query) use ($id) {
            // This callback is not used in whereJsonContains - we need to use where with JSON
        })
        ->orWhere(function($query) use ($id) {
            // Raw query to check if product ID exists in items JSON array
            $query->whereRaw("JSON_CONTAINS(items, JSON_ARRAY(?)) OR items LIKE ?", [$id, '%"product_id":' . $id . '%']);
        })
        ->orderByDesc('created_at')
        ->get();

        // If first query returns no results, try alternative approach
        if ($orders->isEmpty()) {
            $orders = \App\Models\Order::get()->filter(function($order) use ($id) {
                if (!is_array($order->items)) {
                    return false;
                }
                // Check if any item in the order contains this product ID
                foreach ($order->items as $item) {
                    if (isset($item['product_id']) && $item['product_id'] == $id) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        // Group orders by status/category
        $ordersByCategory = [
            'pesan' => $orders->where('status', 'pending')->values(),
            'proses' => $orders->where('status', 'processing')->values(),
            'completed' => $orders->where('status', 'completed')->values(),
            'cancel' => $orders->where('status', 'cancelled')->values(),
            'batal' => $orders->where('status', 'rejected')->values(),
        ];

        // Collect all variant images for carousel
        $variantImages = [];
        if ($product->variants) {
            foreach ($product->variants as $variant) {
                if ($variant->image) {
                    $imageUrl = str_starts_with($variant->image, 'http://') || str_starts_with($variant->image, 'https://') 
                        ? $variant->image 
                        : asset('storage/' . $variant->image);
                    $variantImages[] = $imageUrl;
                }
            }
        }
        
        // If no variant images but product has main image, use main image
        if (empty($variantImages) && $product->image) {
            $imageUrl = str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://') 
                ? $product->image 
                : asset('storage/' . $product->image);
            $variantImages[] = $imageUrl;
        }

        return view('admin.all-products-detail', [
            'product' => $product,
            'orders' => $orders,
            'ordersByCategory' => $ordersByCategory,
            'variantImages' => $variantImages,
        ]);
    }

    /**
     * Get all products with filters (for AJAX)
     */
    public function getProducts(Request $request)
    {
        try {
            $query = Product::with('variants');

            // Filter by search query
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Filter by status (tab)
            if ($request->filled('status') && $request->status !== 'ALL') {
                switch ($request->status) {
                    case 'ACTIVE':
                        $query->where('is_active', true);
                        break;
                    case 'DRAFT':
                    case 'ARCHIVED':
                        $query->where('is_active', false);
                        break;
                    case 'READY':
                        $query->where('stock', '>', 0);
                        break;
                    case 'HABIS':
                        $query->where('stock', '=', 0);
                        break;
                }
            }

            // Filter by category
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Sorting
            $sortBy = $request->get('sortBy', 'id');
            $sortOrder = $request->get('sortOrder', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('perPage', 10);
            $products = $query->paginate($perPage);

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

            return response()->json([
                'success' => true,
                'data' => $productsData->toArray(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product by ID
     */
    public function show($id)
    {
        try {
            $product = Product::with(['variants', 'customDesignPrices'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Store a new product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:topi,kaos,polo,jaket,jersey,celana,lainnya',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'subcategory' => 'nullable|string|max:255',
            'original_price' => 'nullable|numeric|min:0',
            'colors' => 'nullable|string', // JSON string from frontend
            'sizes' => 'nullable|string',  // JSON string from frontend
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240', // Naikkan limit ke 10MB, akan dikompres otomatis
            'variant_images' => 'nullable|array',
            'variant_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Prepare data
            $data = [
                'name' => $request->name,
                'category' => $request->category,
                'price' => $request->price,
                'stock' => $request->stock,
                'description' => $request->description,
                'subcategory' => $request->subcategory,
                'original_price' => $request->original_price,
                'is_active' => $request->get('is_active', true),
                'custom_design_allowed' => $request->get('custom_design_allowed', false),
            ];
            
            // Enforce: Kategori lainnya, topi, celana tidak bisa custom design
            $noCustomDesignCategories = ['lainnya', 'topi', 'celana'];
            if (in_array($request->category, $noCustomDesignCategories)) {
                $data['custom_design_allowed'] = false;
            }
            
            // Generate slug from name
            $data['slug'] = Str::slug($request->name);
            
            // Ensure unique slug
            $originalSlug = $data['slug'];
            $count = 1;
            while (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $count;
                $count++;
            }

            // Parse colors from JSON string
            if ($request->filled('colors')) {
                $colors = $request->colors;
                $data['colors'] = is_string($colors) ? json_decode($colors, true) : $colors;
            }

            // Parse sizes from JSON string
            if ($request->filled('sizes')) {
                $sizes = $request->sizes;
                $data['sizes'] = is_string($sizes) ? json_decode($sizes, true) : $sizes;
            }

            // === HANDLE IMAGE UPLOAD DENGAN KOMPRESI ===
            if ($request->hasFile('images')) {
                $imagePaths = [];
                
                // Loop setiap gambar yang diupload
                foreach ($request->file('images') as $image) {
                    // 🎯 PANGGIL METHOD KOMPRESI
                    // Method ini akan:
                    // 1. Resize jika terlalu besar
                    // 2. Kompres dengan quality 85%
                    // 3. Convert ke WebP (lebih kecil 30-50%)
                    // 4. Return path file yang sudah dikompresi
                    $path = $this->compressAndStoreImage($image, 'products');
                    $imagePaths[] = $path;
                }
                
                $data['images'] = $imagePaths;
                $data['image'] = $imagePaths[0] ?? null; // First image as main
            }

            // Auto-disable if stock is 0 (Habis)
            if ($data['stock'] == 0) {
                $data['is_active'] = false;
            }

            $product = Product::create($data);

            // Handle variants
            if ($request->filled('variants')) {
                $variants = json_decode($request->variants, true);
                
                // Debug logging
                \Log::info('Variants data:', ['variants' => $variants]);
                \Log::info('Has variant_images:', ['has' => $request->hasFile('variant_images')]);
                if ($request->hasFile('variant_images')) {
                    \Log::info('Variant images:', ['images' => array_keys($request->file('variant_images'))]);
                }
                
                if (is_array($variants)) {
                    foreach ($variants as $index => $variantData) {
                        $variant = [
                            'product_id' => $product->id,
                            'color' => $variantData['color'],
                            'size' => $variantData['size'],
                            'price' => $variantData['price'] ?? 0,
                            'original_price' => $variantData['original_price'] ?? null,
                            'stock' => $variantData['stock'] ?? 0,
                        ];

                        // Handle variant image - check both array formats
                        if ($request->hasFile('variant_images')) {
                            $variantImages = $request->file('variant_images');
                            if (isset($variantImages[$index]) && $variantImages[$index]) {
                                \Log::info("Processing image for variant $index");
                                $variant['image'] = $this->compressAndStoreImage($variantImages[$index], 'variants');
                                \Log::info("Image saved:", ['path' => $variant['image']]);
                            } else {
                                \Log::info("No image found for variant $index");
                            }
                        }

                        ProductVariant::create($variant);
                    }
                }
            }

            // Load variants relationship
            $product->load('variants');

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing product
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:topi,kaos,polo,jaket,jersey,celana,lainnya',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'subcategory' => 'nullable|string|max:255',
            'original_price' => 'nullable|numeric|min:0',
            'colors' => 'nullable|string', // JSON string from frontend
            'sizes' => 'nullable|string',  // JSON string from frontend
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240',
            'variant_images' => 'nullable|array',
            'variant_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use lockForUpdate to prevent race conditions
            $product = Product::lockForUpdate()->findOrFail($id);
            
            // Prepare data
            $data = [
                'name' => $request->name,
                'category' => $request->category,
                'price' => $request->price,
                'stock' => $request->stock,
                'description' => $request->description,
                'subcategory' => $request->subcategory,
                'original_price' => $request->original_price,
                'is_active' => $request->get('is_active', $product->is_active),
                'custom_design_allowed' => $request->get('custom_design_allowed', $product->custom_design_allowed ?? false),
            ];
            
            // Enforce: Kategori lainnya, topi, celana tidak bisa custom design
            $noCustomDesignCategories = ['lainnya', 'topi', 'celana'];
            if (in_array($request->category, $noCustomDesignCategories)) {
                $data['custom_design_allowed'] = false;
            }

            // Update slug if name changed
            if ($request->name !== $product->name) {
                $data['slug'] = Str::slug($request->name);
                
                // Ensure unique slug
                $originalSlug = $data['slug'];
                $count = 1;
                while (Product::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                    $data['slug'] = $originalSlug . '-' . $count;
                    $count++;
                }
            }

            // Parse colors from JSON string
            if ($request->filled('colors')) {
                $colors = $request->colors;
                $data['colors'] = is_string($colors) ? json_decode($colors, true) : $colors;
            }

            // Parse sizes from JSON string
            if ($request->filled('sizes')) {
                $sizes = $request->sizes;
                $data['sizes'] = is_string($sizes) ? json_decode($sizes, true) : $sizes;
            }

            // === HANDLE NEW IMAGE UPLOADS DENGAN KOMPRESI ===
            if ($request->hasFile('images')) {
                // Delete old images
                if ($product->images) {
                    foreach ($product->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $imagePaths = [];
                
                // Loop setiap gambar baru yang diupload
                foreach ($request->file('images') as $image) {
                    // 🎯 PANGGIL METHOD KOMPRESI
                    $path = $this->compressAndStoreImage($image, 'products');
                    $imagePaths[] = $path;
                }
                
                $data['images'] = $imagePaths;
                $data['image'] = $imagePaths[0] ?? null;
            }

            // Auto-disable if stock is 0 (Habis)
            if ($data['stock'] == 0) {
                $data['is_active'] = false;
            }

            // Update product within transaction
            \DB::transaction(function () use ($product, $data, $request) {
                $product->update($data);

                // Handle custom design prices
                if ($request->filled('custom_design_prices') && $data['custom_design_allowed']) {
                    $customPrices = json_decode($request->custom_design_prices, true);
                    
                    if (is_array($customPrices) && count($customPrices) > 0) {
                        // Prepare sync data with pivot attributes
                        $syncData = [];
                        foreach ($customPrices as $priceItem) {
                            $syncData[$priceItem['custom_design_price_id']] = [
                                'custom_price' => $priceItem['custom_price'] ?? 0,
                                'is_active' => $priceItem['is_active'] ?? true,
                            ];
                        }
                        
                        // Sync (will add new, update existing, remove unselected)
                        $product->customDesignPrices()->sync($syncData);
                    } else {
                        // Clear all if no prices selected
                        $product->customDesignPrices()->detach();
                    }
                } elseif (!$data['custom_design_allowed']) {
                    // Clear custom design prices if custom design is disabled
                    $product->customDesignPrices()->detach();
                }

                // Handle variants update
                if ($request->filled('variants')) {
                    // Get old variants to preserve images
                    $oldVariants = $product->variants()->get()->keyBy(function($item) {
                        return $item->color . '-' . $item->size;
                    });
                    
                    // Delete old variant images from storage if needed
                    foreach ($oldVariants as $oldVariant) {
                        if ($oldVariant->image && Storage::disk('public')->exists($oldVariant->image)) {
                            // We'll only delete if not being reused
                        }
                    }
                    
                    // Delete old variants
                    $product->variants()->delete();
                    
                    $variants = json_decode($request->variants, true);
                    
                    if (is_array($variants)) {
                        foreach ($variants as $index => $variantData) {
                            $variant = [
                                'product_id' => $product->id,
                                'color' => $variantData['color'],
                                'size' => $variantData['size'],
                                'price' => $variantData['price'] ?? 0,
                                'original_price' => $variantData['original_price'] ?? null,
                                'stock' => $variantData['stock'] ?? 0,
                            ];

                            // Check if new image uploaded
                            $hasNewImage = false;
                            if ($request->hasFile('variant_images')) {
                                $variantImages = $request->file('variant_images');
                                if (isset($variantImages[$index]) && $variantImages[$index]) {
                                    $variant['image'] = $this->compressAndStoreImage($variantImages[$index], 'variants');
                                    $hasNewImage = true;
                                }
                            }
                            
                            // If no new image, try to keep old image
                            if (!$hasNewImage) {
                                $key = $variantData['color'] . '-' . $variantData['size'];
                                if (isset($oldVariants[$key]) && $oldVariants[$key]->image) {
                                    $variant['image'] = $oldVariants[$key]->image;
                                }
                            }

                            ProductVariant::create($variant);
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->fresh('variants')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete associated images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product status (Active/Draft)
     */
    public function toggleStatus($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->is_active = !$product->is_active;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully',
                'data' => [
                    'id' => $product->id,
                    'is_active' => $product->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk archive products
     */
    public function bulkArchive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updated = Product::whereIn('id', $request->ids)
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} product(s) archived successfully",
                'count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Product::query();

            // Apply same filters as getProducts
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status') && $request->status !== 'ALL') {
                switch ($request->status) {
                    case 'ACTIVE':
                        $query->where('is_active', true);
                        break;
                    case 'DRAFT':
                    case 'ARCHIVED':
                        $query->where('is_active', false);
                        break;
                    case 'READY':
                        $query->where('stock', '>', 0);
                        break;
                    case 'HABIS':
                        $query->where('stock', '=', 0);
                        break;
                }
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            $products = $query->orderBy('created_at', 'desc')->get();

            // Generate CSV
            $filename = 'products_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($products) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Slug',
                    'Category',
                    'Subcategory',
                    'Price',
                    'Original Price',
                    'Stock',
                    'Status',
                    'Colors',
                    'Sizes',
                    'Created At'
                ]);

                // CSV Data
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->id,
                        $product->name,
                        $product->slug,
                        $product->category,
                        $product->subcategory,
                        $product->price,
                        $product->original_price,
                        $product->stock,
                        $product->is_active ? 'Active' : 'Draft',
                        is_array($product->colors) ? implode(', ', $product->colors) : '',
                        is_array($product->sizes) ? implode(', ', $product->sizes) : '',
                        $product->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎯 HELPER METHOD: Compress and optimize uploaded image
     * 
     * LOGIKA KOMPRESI AGRESIF:
     * 1. Baca file yang diupload
     * 2. Cek dimensi dan resize proportional
     * 3. Kompres dengan quality dinamis hingga ukuran < 2MB
     * 4. Convert ke WebP untuk ukuran lebih kecil
     * 5. Jika masih > 2MB, turunkan quality secara bertahap
     * 6. Simpan ke storage
     * 
     * @param \Illuminate\Http\UploadedFile $file - File yang diupload
     * @param string $directory - Folder tujuan (default: 'products')
     * @return string - Path file yang sudah dikompresi
     */
    private function compressAndStoreImage($file, $directory = 'products')
    {
        // === KONFIGURASI ===
        $maxWidth = 1500;         // Lebar maksimal dalam pixel
        $maxHeight = 1500;        // Tinggi maksimal dalam pixel
        $targetSizeKB = 2048;     // Target ukuran maksimal 2MB (2048 KB)
        $initialQuality = 85;     // Quality awal
        $minQuality = 50;         // Quality minimal (jangan terlalu rendah agar tetap bagus)
        $convertToWebp = true;    // Set false jika tidak mau convert ke WebP
        
        try {
            // === STEP 0: Ensure directory exists ===
            $fullPath = storage_path('app/public/' . $directory);
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0755, true);
                \Log::info("Created storage directory: {$fullPath}");
            }
            
            // === STEP 1: Initialize ImageManager dengan GD driver ===
            $manager = new ImageManager(new Driver());
            
            // === STEP 2: Load gambar ===
            $image = $manager->read($file);
            
            // === STEP 3: Cek dimensi gambar ===
            $width = $image->width();
            $height = $image->height();
            
            // === STEP 4: Resize jika melebihi batas ===
            if ($width > $maxWidth || $height > $maxHeight) {
                $image->scale(width: $maxWidth, height: $maxHeight);
            }
            
            // === STEP 5: Generate nama file unik ===
            $filename = Str::random(40);
            
            // === STEP 6: Kompresi dengan quality dinamis hingga ukuran < 2MB ===
            $quality = $initialQuality;
            $encodedImage = null;
            $attempts = 0;
            $maxAttempts = 10; // Maksimal 10 percobaan
            
            do {
                $attempts++;
                
                if ($convertToWebp) {
                    $filename = str_replace(['.jpg', '.jpeg', '.png', '.webp'], '', $filename) . '.webp';
                    $encodedImage = $image->toWebp($quality);
                } else {
                    $extension = $file->getClientOriginalExtension();
                    $filename = str_replace(['.jpg', '.jpeg', '.png', '.webp'], '', $filename) . '.' . $extension;
                    
                    if (in_array($extension, ['jpg', 'jpeg'])) {
                        $encodedImage = $image->toJpeg($quality);
                    } elseif ($extension === 'png') {
                        // PNG tidak support quality, convert ke JPG jika terlalu besar
                        if ($attempts > 1) {
                            $filename = str_replace('.png', '.jpg', $filename);
                            $encodedImage = $image->toJpeg($quality);
                        } else {
                            $encodedImage = $image->toPng();
                        }
                    } elseif ($extension === 'webp') {
                        $encodedImage = $image->toWebp($quality);
                    } else {
                        $encodedImage = $image->toJpeg($quality);
                    }
                }
                
                // Cek ukuran hasil kompresi
                $sizeKB = strlen($encodedImage) / 1024; // Convert bytes ke KB
                
                // Jika ukuran sudah < 2MB, selesai
                if ($sizeKB <= $targetSizeKB) {
                    break;
                }
                
                // Jika masih terlalu besar, turunkan quality
                // Rumus: turunkan 5-10 poin per iterasi tergantung seberapa besar file
                if ($sizeKB > $targetSizeKB * 2) {
                    $quality -= 15; // File sangat besar, turunkan drastis
                } elseif ($sizeKB > $targetSizeKB * 1.5) {
                    $quality -= 10; // File cukup besar
                } else {
                    $quality -= 5;  // File sedikit besar
                }
                
                // Pastikan quality tidak di bawah minimum
                if ($quality < $minQuality) {
                    $quality = $minQuality;
                }
                
                // Jika sudah di quality minimal tapi masih besar, resize lebih kecil lagi
                if ($quality === $minQuality && $sizeKB > $targetSizeKB) {
                    $maxWidth = (int)($maxWidth * 0.8); // Kurangi 20%
                    $maxHeight = (int)($maxHeight * 0.8);
                    $image->scale(width: $maxWidth, height: $maxHeight);
                }
                
            } while ($sizeKB > $targetSizeKB && $attempts < $maxAttempts);
            
            // === STEP 7: Simpan ke storage ===
            $path = $directory . '/' . $filename;
            $fullFilePath = storage_path('app/public/' . $path);
            file_put_contents($fullFilePath, (string) $encodedImage);
            
            // Verify file was saved
            if (!file_exists($fullFilePath)) {
                throw new \Exception("Failed to write image file to: {$fullFilePath}");
            }
            
            // Also save via Storage facade for consistency
            Storage::disk('public')->put($path, (string) $encodedImage);
            
            // Log info untuk monitoring
            $finalSizeKB = round(strlen($encodedImage) / 1024, 2);
            \Log::info("Image compressed: {$file->getClientOriginalName()} | Original: " . 
                      round($file->getSize() / 1024, 2) . "KB | Compressed: {$finalSizeKB}KB | Quality: {$quality} | Saved to: {$path}");
            
            // === STEP 8: Return path untuk disimpan ke database ===
            return $path;
            
        } catch (\Exception $e) {
            // Jika kompresi gagal, fallback ke upload biasa
            \Log::error('Image compression failed: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            try {
                $storedPath = $file->store($directory, 'public');
                \Log::info("Fallback: Image stored at {$storedPath}");
                return $storedPath;
            } catch (\Exception $fallbackError) {
                \Log::error('Image storage fallback failed: ' . $fallbackError->getMessage());
                throw new \Exception('Unable to store image: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get orders for a specific product (AJAX endpoint for real-time sync)
     */
    public function getProductOrders($productId)
    {
        try {
            // Query all orders containing this product
            $orders = \App\Models\Order::get()->filter(function($order) use ($productId) {
                if (!is_array($order->items)) {
                    return false;
                }
                // Check if any item in the order contains this product ID
                foreach ($order->items as $item) {
                    if (isset($item['product_id']) && $item['product_id'] == $productId) {
                        return true;
                    }
                }
                return false;
            })->values()->sortByDesc('created_at');

            // Group orders by status
            $ordersByCategory = [
                'pesan' => $orders->where('status', 'pending')->values(),
                'proses' => $orders->where('status', 'processing')->values(),
                'completed' => $orders->where('status', 'completed')->values(),
                'cancel' => $orders->where('status', 'cancelled')->values(),
                'batal' => $orders->where('status', 'rejected')->values(),
            ];

            // Format orders for JSON response
            $formattedOrders = $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user?->name ?? 'Unknown',
                    'status' => $order->status,
                    'status_label' => $this->getStatusLabel($order->status),
                    'total' => (float)$order->total,
                    'total_formatted' => 'Rp ' . number_format((float)$order->total, 0, ',', '.'),
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                ];
            })->values();

            // Format categories with counts
            $categories = [
                'all' => count($orders),
                'pesan' => count($ordersByCategory['pesan']),
                'proses' => count($ordersByCategory['proses']),
                'completed' => count($ordersByCategory['completed']),
                'cancel' => count($ordersByCategory['cancel']),
                'batal' => count($ordersByCategory['batal']),
            ];

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders,
                'categories' => $categories,
                'total_orders' => count($orders),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching product orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to get status label
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'Pesan',
            'processing' => 'Proses',
            'completed' => 'Completed',
            'cancelled' => 'Cancel',
            'rejected' => 'Batal',
            default => ucfirst($status),
        };
    }
}
