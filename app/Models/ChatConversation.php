<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'product_id', 
        'status', 
        'subject',
        'admin_id',
        'taken_over_by_admin',
        'taken_over_at',
        'needs_admin_response',
        'needs_response_since',
        'is_admin_active',
        'keywords',
        'subcategory_id',
        'chat_source',
        'expires_at'
    ];

    protected $casts = [
        'taken_over_by_admin' => 'boolean',
        'needs_admin_response' => 'boolean',
        'is_admin_active' => 'boolean',
        'keywords' => 'array',
        'taken_over_at' => 'datetime',
        'needs_response_since' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id', 'id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id', 'id')->latest();
    }

    /**
     * Check if conversation is currently being handled by admin
     */
    public function isActivelyHandledByAdmin(): bool
    {
        return $this->taken_over_by_admin && $this->admin_id !== null;
    }

    /**
     * Get admin handling this conversation
     */
    public function getHandlingAdmin()
    {
        return $this->admin;
    }

    /**
     * Check if customer needs immediate admin response
     */
    public function isWaitingForAdminResponse(): bool
    {
        return $this->needs_admin_response;
    }
}