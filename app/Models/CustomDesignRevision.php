<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomDesignRevision extends Model
{
    protected $fillable = [
        'custom_design_order_id',
        'type',
        'note',
        'section_name',
        'file_path',
        'file_name',
        'previous_file_path',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomDesignOrder::class, 'custom_design_order_id');
    }
}