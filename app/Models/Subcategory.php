<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subcategory extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'slug',
        'category',
    ];

    /**
     * Get the products for this subcategory
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
