<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
        'token',
        'status',
        'expires_at',
        'is_confirmed',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_confirmed' => 'boolean',
    ];

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
