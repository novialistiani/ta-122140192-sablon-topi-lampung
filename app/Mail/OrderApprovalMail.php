<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        \Log::info('Building approval email', [
            'order_id' => $this->order->id,
            'user_email' => $this->order->user->email,
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
            ]
        ]);

        return $this->subject('Pesanan Anda Telah Disetujui - Harap Segera Lakukan Pembayaran')
            ->markdown('emails.orders.approved', [
                'order' => $this->order,
                'paymentDeadline' => now()->addHours(24)->format('d M Y H:i'),
                'vaGenerateDeadline' => now()->addHours(24)->format('d M Y H:i')
            ]);
    }
}