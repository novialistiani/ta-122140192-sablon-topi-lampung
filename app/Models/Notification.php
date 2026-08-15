<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'data',
        'action_url',
        'action_text',
        'priority',
        'read_at',
        'archived_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Get the notifiable model (User or Admin)
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'read_at' => now(),
        ]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for specific user (polymorphic)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('notifiable_type', 'App\\Models\\User')
                     ->where('notifiable_id', $userId);
    }

    /**
     * Scope for specific admin (polymorphic)
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('notifiable_type', 'App\\Models\\Admin')
                     ->where('notifiable_id', $adminId);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
