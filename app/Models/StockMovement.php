<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_label',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'reason',
        'reference_type',
        'reference_id',
        'admin_id',
        'note',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public const REASON_LABELS = [
        'order_approved' => 'Pesanan disetujui',
        'order_restored' => 'Stok dikembalikan (pesanan ditolak/dibatalkan)',
        'pos_sale' => 'Penjualan kasir (POS)',
        'manual_adjustment' => 'Penyesuaian manual admin',
        'initial_stock' => 'Stok awal produk',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASON_LABELS[$this->reason] ?? $this->reason;
    }
}