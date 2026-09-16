<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\CustomDesignOrder;
use App\Traits\StockManagementTrait;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    use StockManagementTrait;

    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel approved orders whose payment_deadline has passed without payment proof uploaded';

    public function handle()
    {
        $totalCancelled = 0;

        // ==== Pesanan Reguler ====
        $expiredRegularOrders = Order::where('status', 'approved')
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<=', now())
            ->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', 'unpaid');
            })
            ->get();

        foreach ($expiredRegularOrders as $order) {
            $this->cancelOrder($order, 'regular');
            $totalCancelled++;
        }

        // ==== Pesanan Custom Design ====
        $expiredCustomOrders = CustomDesignOrder::where('status', 'approved')
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<=', now())
            ->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', 'unpaid');
            })
            ->get();

        foreach ($expiredCustomOrders as $order) {
            $this->cancelOrder($order, 'custom');
            $totalCancelled++;
        }

        $this->info("Auto-cancel selesai. Total pesanan dibatalkan: {$totalCancelled}");
    }

    /**
     * Batalkan satu pesanan: kembalikan stok, ubah status, kirim notifikasi.
     */
    protected function cancelOrder($order, string $orderType): void
    {
        // Kembalikan stok yang sudah dikurangi saat approve
        $this->restoreStockForOrder($order, $orderType);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'admin_notes' => 'Dibatalkan otomatis oleh sistem karena tidak ada pembayaran hingga batas waktu (payment_deadline) terlampaui.',
        ]);

        // Kirim notifikasi in-app ke pelanggan (menggunakan service yang sudah ada)
        try {
            if ($order->user_id) {
                app(\App\Services\NotificationService::class)->notifyOrderStatusUpdate(
                    $order,
                    $order->user_id,
                    'approved',
                    'cancelled'
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send auto-cancel notification', [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'error' => $e->getMessage(),
            ]);
        }

        \Log::info("Order auto-cancelled due to unpaid deadline", [
            'order_id' => $order->id,
            'order_type' => $orderType,
            'approved_at' => $order->approved_at,
        ]);
    }
}