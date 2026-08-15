<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'virtual_account_id',
        'order_type',
        'order_id',
        'payment_method',
        'payment_channel',
        'amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function booting()
    {
        static::creating(function ($model) {
            if (!$model->transaction_id) {
                $model->transaction_id = self::generateTransactionId();
            }
        });
    }

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the transaction
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the virtual account associated with the transaction
     */
    public function virtualAccount(): BelongsTo
    {
        return $this->belongsTo(VirtualAccount::class);
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        // Update order payment_status to 'paid' if order status is approved
        if ($this->order_id && $this->order_type) {
            if ($this->order_type === 'custom') {
                $order = \App\Models\CustomDesignOrder::find($this->order_id);
                if ($order && $order->status === 'approved') {
                    $order->update([
                        'payment_status' => 'paid',
                        'paid_at' => now()
                    ]);
                }
            } else {
                $order = \App\Models\Order::find($this->order_id);
                if ($order && $order->status === 'approved') {
                    $order->update([
                        'payment_status' => 'paid',
                        'paid_at' => now()
                    ]);
                }
            }
        }
    }
}
