<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomDesignOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'product_name',
        'product_price',
        'quantity',
        'cutting_type',
        'special_materials',
        'additional_description',
        'status',
        'payment_status',
        'total_price',
        'admin_notes',
        'rejected_at',
        'approved_at',
        'payment_deadline',
        'processing_at',
        'completed_at',
    ];

    protected $casts = [
        'special_materials' => 'array',
        'product_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'rejected_at' => 'datetime',
        'approved_at' => 'datetime',
        'payment_deadline' => 'datetime',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the custom design order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with this order
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant associated with this order
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get all uploaded files for this order
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(CustomDesignUpload::class);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->total_price, 0, ',', '.');
    }
}
