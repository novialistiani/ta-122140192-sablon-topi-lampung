<?php

namespace App\Console\Commands;

use App\Mail\OrderCancellationMail;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel orders that have exceeded their payment deadlines';

    public function handle()
    {
        // Cancel orders where VA hasn't been generated within 24 hours of approval
        $expiredApprovedOrders = Order::where('status', 'approved')
            ->where('approved_at', '<=', now()->subHours(24))
            ->where('va_number', null)
            ->get();

        foreach ($expiredApprovedOrders as $order) {
            $order->update(['status' => 'cancelled']);
            
            Mail::to($order->user->email)->send(new OrderCancellationMail(
                $order,
                'Batas waktu generate Virtual Account (24 jam) telah terlewati'
            ));
        }

        // Cancel orders where payment hasn't been completed within 1 hour of VA generation
        $expiredVaOrders = Order::where('status', 'pending_payment')
            ->where('va_generated_at', '<=', now()->subHour())
            ->where('payment_status', 'unpaid')
            ->get();

        foreach ($expiredVaOrders as $order) {
            $order->update(['status' => 'cancelled']);
            
            Mail::to($order->user->email)->send(new OrderCancellationMail(
                $order,
                'Batas waktu pembayaran (1 jam) setelah generate VA telah terlewati'
            ));
        }

        $this->info('Expired orders have been cancelled');
    }
}