<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDesignPrice extends Model
{
    protected $fillable = [
        'type',
        'code',
        'name',
        'price',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Scope untuk upload sections
    public function scopeUploadSections($query)
    {
        return $query->where('type', 'upload_section')->where('is_active', true);
    }

    // Scope untuk cutting types
    public function scopeCuttingTypes($query)
    {
        return $query->where('type', 'cutting_type')->where('is_active', true);
    }
}
