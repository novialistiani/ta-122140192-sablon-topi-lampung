<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color',
        'size',
        'price',
        'original_price',
        'stock',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
    ];

    /**
     * Accessor untuk memastikan image URL selalu valid
     * Supports Hostinger LiteSpeed /public/storage/ path
     */
    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        return image_url($value);
    }

    // Relationship to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
