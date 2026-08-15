<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'notification_id',
        'channel',
        'recipient_type',
        'recipient_id',
        'recipient_email',
        'recipient_phone',
        'subject',
        'message_id',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'failed_at',
        'error_message',
        'retry_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'failed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the notification
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Get the recipient (polymorphic)
     */
    public function recipient()
    {
        return $this->morphTo('recipient', 'recipient_type', 'recipient_id');
    }

    /**
     * Scope for specific channel
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope for specific status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending logs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Update status with timestamp
     */
    public function updateStatus(string $status, ?string $errorMessage = null): void
    {
        $data = ['status' => $status];
        
        $timestampField = match($status) {
            'sent' => 'sent_at',
            'delivered' => 'delivered_at',
            'opened' => 'opened_at',
            'clicked' => 'clicked_at',
            'bounced' => 'bounced_at',
            'failed' => 'failed_at',
            default => null,
        };

        if ($timestampField) {
            $data[$timestampField] = now();
        }

        if ($errorMessage) {
            $data['error_message'] = $errorMessage;
        }

        $this->update($data);
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): void
    {
        $this->increment('retry_count');
    }
}
