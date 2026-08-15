<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_type',
        'order_id',
        'bank_code',
        'va_number',
        'amount',
        'status',
        'expired_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the VA
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that owns this VA
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if VA is expired
     */
    public function isExpired()
    {
        return $this->expired_at && $this->expired_at->isPast() && $this->status === 'pending';
    }

    /**
     * Check if VA is active (not paid, not expired, not cancelled)
     */
    public function isActive()
    {
        return $this->status === 'pending' && $this->expired_at && $this->expired_at->isFuture();
    }

    /**
     * Mark VA as expired
     */
    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark VA as paid and update ONLY the specific order linked to this VA (1 VA = 1 Order)
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        // Update only the specific order linked to this VA
        if ($this->order_type && $this->order_id) {
            if ($this->order_type === 'custom') {
                \App\Models\CustomDesignOrder::where('id', $this->order_id)
                    ->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'status' => 'processing' // Move to processing after payment
                    ]);
            } else {
                \App\Models\Order::where('id', $this->order_id)
                    ->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'status' => 'processing' // Move to processing after payment
                    ]);
            }
            
            // Update associated payment transaction for this specific order
            \App\Models\PaymentTransaction::where('user_id', $this->user_id)
                ->where('order_type', $this->order_type)
                ->where('order_id', $this->order_id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            
            \Log::info("VA #{$this->id} marked as paid for Order #{$this->order_id} ({$this->order_type})");
        }
    }

    /**
     * Generate VA number based on bank code
     */
    public static function generateVANumber($bankCode, $userId)
    {
        $timestamp = now()->format('His'); // HHMMSS
        $userPadded = str_pad($userId, 6, '0', STR_PAD_LEFT);
        $random = rand(100, 999);
        
        // Bank prefix mapping
        $prefixes = [
            'bca' => '70012',
            'bni' => '8808',
            'bri' => '88810',
            'permata' => '8528',
        ];
        
        $prefix = $prefixes[$bankCode] ?? '88888';
        
        return $prefix . $userPadded . $random;
    }
}
