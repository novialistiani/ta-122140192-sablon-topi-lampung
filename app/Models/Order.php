<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'user_id',
        'customer_address_id',
        'payment_method_id',
        'order_number',
        'items',
        'subtotal',
        'shipping_cost',
        'discount',
        'total',
        'status',
        'payment_status',
        'shipping_service',
        'customer_notes',
        'admin_notes',
        'paid_at',
        'processing_at',
        'completed_at',
        'cancelled_at',
        'approved_at',
        'rejected_at',
        'payment_deadline',
        'confirmation_deadline',
        'va_number',
        'va_generated_at',
    ];

    protected $casts = [
        'items' => 'json',  // Use json cast for proper array handling
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'processing_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'payment_deadline' => 'datetime',
        'confirmation_deadline' => 'datetime',
        'va_generated_at' => 'datetime',
    ];

    /**
     * Append custom attributes
     */
    protected $appends = [
        'last_action_timestamp',
        'formatted_last_action',
    ];

    /**
     * Boot method untuk auto-generate order number
     */
    protected static function boot()
    {
        parent::boot();
        
        // Don't need retrieved hook anymore - json cast handles it
        // static::retrieved(function ($model) { ... });
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                // Use OrderNumberSequence for atomic order number generation
                try {
                    $order->order_number = \App\Models\OrderNumberSequence::getNextOrderNumber();
                } catch (\Exception $e) {
                    \Log::error('Failed to generate order number: ' . $e->getMessage());
                    throw $e;
                }
            }

            // Set confirmation deadline ke 24 jam dari sekarang
            if (empty($order->confirmation_deadline)) {
                $order->confirmation_deadline = \Carbon\Carbon::now()->addHours(24);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->total, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak diketahui',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Belum dibayar',
            'va_active' => 'Virtual Account Aktif',
            'paid' => 'Sudah dibayar',
            'processing' => 'Diproses',
            'failed' => 'Pembayaran gagal',
            'refunded' => 'Dana dikembalikan',
            default => 'Tidak diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'status-pending',
            'approved' => 'status-approved',
            'rejected' => 'status-rejected',
            'processing' => 'status-processing',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
            default => 'status-default',
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((float)$this->total, 0, ',', '.');
    }

    public function getItemsTotalQuantityAttribute(): int
    {
        // Calculate total quantity from JSON items array
        return collect($this->items)->sum('quantity');
    }

    /**
     * Get the most recent action timestamp based on order status
     * This returns the actual timestamp when the current status was set
     */
    public function getLastActionTimestampAttribute()
    {
        // Return the timestamp of the most recent status change
        return match ($this->status) {
            'completed' => $this->completed_at ?? $this->created_at,
            'cancelled' => $this->cancelled_at ?? $this->created_at,
            'processing' => $this->processing_at ?? $this->created_at,
            'paid' => $this->paid_at ?? $this->created_at,
            'approved' => $this->approved_at ?? $this->created_at,
            'rejected' => $this->rejected_at ?? $this->created_at,
            default => $this->created_at,
        };
    }

    /**
     * Get formatted last action timestamp
     */
    public function getFormattedLastActionAttribute(): string
    {
        $timestamp = $this->last_action_timestamp;
        return $timestamp ? $timestamp->format('M d, Y H:i') : 'N/A';
    }
}
