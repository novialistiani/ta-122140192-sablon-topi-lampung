<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'chat_conversation_id', // backward compatibility
        'user_id',
        'message',
        'sender_type',
        'is_escalated',
        'is_admin_reply',
        'is_read_by_user',
        'is_read_by_admin',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_escalated' => 'boolean',
        'is_admin_reply' => 'boolean',
        'is_read_by_user' => 'boolean',
        'is_read_by_admin' => 'boolean'
    ];

    public function conversation()
    {
        // Support both column names for backward compatibility
        return $this->belongsTo(ChatConversation::class, 'conversation_id', 'id');
    }

    /**
     * Check if message is from admin
     */
    public function isFromAdmin(): bool
    {
        return $this->sender_type === 'admin' || $this->is_admin_reply;
    }

    /**
     * Check if message is from user
     */
    public function isFromUser(): bool
    {
        return $this->sender_type === 'user';
    }

    /**
     * Check if message is from bot
     */
    public function isFromBot(): bool
    {
        return $this->sender_type === 'bot';
    }

    /**
     * Mark message as read by user
     */
    public function markAsReadByUser()
    {
        $this->update(['is_read_by_user' => true]);
    }

    /**
     * Mark message as read by admin
     */
    public function markAsReadByAdmin()
    {
        $this->update(['is_read_by_admin' => true]);
    }
}