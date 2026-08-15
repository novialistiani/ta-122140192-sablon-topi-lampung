<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomDesignUpload extends Model
{
    protected $fillable = [
        'custom_design_order_id',
        'section_name',
        'file_path',
        'file_name',
        'file_size',
    ];

    /**
     * Get the custom design order that owns this upload
     */
    public function customDesignOrder(): BelongsTo
    {
        return $this->belongsTo(CustomDesignOrder::class);
    }

    /**
     * Get the full URL of the uploaded file
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the file URL as accessor (alternative name)
     */
    public function getFilePathUrlAttribute()
    {
        $url = $this->file_url;
        // Jika sudah full URL, return langsung
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        // Otherwise, ensure it has asset() wrapper
        return asset($url);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
