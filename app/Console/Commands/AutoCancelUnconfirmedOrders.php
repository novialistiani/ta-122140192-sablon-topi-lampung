<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AutoCancelUnconfirmedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-cancel-unconfirmed';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically cancel orders that were not confirmed by admin within 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = Carbon::now();
            
            // Cari pesanan yang:
            // 1. Status masih 'pending' (belum dikonfirmasi)
            // 2. Sudah melewati confirmation_deadline (24 jam dari dibuat)
            $unconfirmedOrders = Order::where('status', 'pending')
                ->where('confirmation_deadline', '<=', $now)
                ->get();

            if ($unconfirmedOrders->isEmpty()) {
                $this->info('✓ Tidak ada pesanan yang perlu dibatalkan.');
                return Command::SUCCESS;
            }

            $cancelledCount = 0;
            $failedCount = 0;

            foreach ($unconfirmedOrders as $order) {
                try {
                    DB::beginTransaction();

                    // Update status pesanan menjadi cancelled
                    $order->update([
                        'status' => 'cancelled',
                        'cancelled_at' => $now,
                        'admin_notes' => ($order->admin_notes ? $order->admin_notes . "\n" : '') . 
                                        "Auto-cancelled pada " . $now->format('Y-m-d H:i:s') . " karena admin tidak mengkonfirmasi dalam 24 jam."
                    ]);

                    // Restore stock untuk setiap item dalam pesanan
                    if ($order->items && is_array($order->items)) {
                        foreach ($order->items as $item) {
                            if (isset($item['product_id'])) {
                                // Update product stock
                                DB::table('products')
                                    ->where('id', $item['product_id'])
                                    ->increment('stock', $item['quantity'] ?? 1);
                            }

                            if (isset($item['variant_id'])) {
                                // Update variant stock
                                DB::table('product_variants')
                                    ->where('id', $item['variant_id'])
                                    ->increment('stock', $item['quantity'] ?? 1);
                            }
                        }
                    }

                    DB::commit();
                    $cancelledCount++;

                    // Log activity
                    $this->info("✓ Pesanan #{$order->order_number} berhasil dibatalkan otomatis");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $failedCount++;
                    $this->error("✗ Gagal membatalkan pesanan #{$order->order_number}: " . $e->getMessage());
                    \Log::error("Auto-cancel error for order #{$order->order_number}: " . $e->getMessage());
                }
            }

            // Summary
            $this->line('');
            $this->info("Ringkasan:");
            $this->info("├─ Pesanan dibatalkan: {$cancelledCount}");
            $this->info("├─ Pesanan gagal: {$failedCount}");
            $this->info("└─ Total diproses: " . count($unconfirmedOrders));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            \Log::error('AutoCancelUnconfirmedOrders error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
