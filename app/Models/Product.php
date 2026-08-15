<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'subcategory',
        'price',
        'original_price',
        'image',
        'images',
        'colors',
        'sizes',
        'stock',
        'views',
        'sales',
        'is_featured',
        'is_active',
        'custom_design_allowed',
    ];

    protected $casts = [
        'images' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'custom_design_allowed' => 'boolean',
    ];

    /**
     * Accessor untuk memastikan image URL selalu valid
     * Menangani baik local path maupun external URLs
     * Supports Hostinger LiteSpeed /public/storage/ path
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        return image_url($value);
    }

    /**
     * Accessor untuk images array
     * Memastikan setiap image di array memiliki URL yang valid
     * Supports Hostinger LiteSpeed /public/storage/ path
     */
    public function getImagesAttribute($value)
    {
        if (!$value || !is_array($value)) {
            return [];
        }
        
        return array_map(function($image) {
            if (!$image) {
                return null;
            }
            
            return image_url($image);
        }, $value);
    }

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeReady($query)
    {
        return $query->where('is_active', true)
                     ->where('stock', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSubcategory($query, $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Relationship to ProductVariant
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeFilterByColors($query, array $colors)
    {
        return $query->where(function($q) use ($colors) {
            foreach ($colors as $color) {
                $q->orWhereJsonContains('colors', $color);
            }
        });
    }

    public function scopeFilterBySizes($query, array $sizes)
    {
        return $query->where(function($q) use ($sizes) {
            foreach ($sizes as $size) {
                $q->orWhereJsonContains('sizes', $size);
            }
        });
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeSortBy($query, $sort)
    {
        switch ($sort) {
            case 'newest':
                return $query->orderBy('created_at', 'desc');
            case 'price_low':
                return $query->orderBy('price', 'asc');
            case 'price_high':
                return $query->orderBy('price', 'desc');
            case 'popular':
                return $query->orderBy('views', 'desc');
            default: // most_popular
                return $query->orderBy('sales', 'desc');
        }
    }

    // Increment views
    public function incrementViews()
    {
        $this->increment('views');
    }

    // Check if product is in stock
    public function inStock()
    {
        return $this->stock > 0;
    }

    // Check if product is ready (active + in stock)
    public function isReady()
    {
        return $this->is_active && $this->stock > 0;
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return number_format((float)$this->price, 0, ',', '.');
    }

    // Get variant images for carousel
    public function getVariantImagesAttribute()
    {
        $images = [];
        
        // Add main product image first if exists
        if ($this->image) {
            // Check if image is already a full URL (external)
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                $images[] = $this->image;
            } else {
                $images[] = asset('storage/' . $this->image);
            }
        }
        
        // Add variant images
        if ($this->relationLoaded('variants') && $this->variants->count() > 0) {
            foreach ($this->variants as $variant) {
                if ($variant->image) {
                    // Check if variant image is external URL
                    if (str_starts_with($variant->image, 'http://') || str_starts_with($variant->image, 'https://')) {
                        $variantImage = $variant->image;
                    } else {
                        $variantImage = asset('storage/' . $variant->image);
                    }
                    
                    // Avoid duplicates
                    if (!in_array($variantImage, $images)) {
                        $images[] = $variantImage;
                    }
                }
            }
        }
        
        // If no images found, add from images array
        if (empty($images) && !empty($this->images) && is_array($this->images)) {
            foreach ($this->images as $img) {
                // Check if image in array is external URL
                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                    $images[] = $img;
                } else {
                    $images[] = asset('storage/' . $img);
                }
            }
        }
        
        return $images;
    }

    // Relationship to CustomDesignPrices (Many-to-Many)
    public function customDesignPrices()
    {
        return $this->belongsToMany(
            CustomDesignPrice::class,
            'product_custom_design_prices',
            'product_id',
            'custom_design_price_id'
        )->withPivot('custom_price', 'is_active')->withTimestamps();
    }

    public function chatConversations()
    {
        return $this->hasMany(ChatConversation::class);
    }
}
