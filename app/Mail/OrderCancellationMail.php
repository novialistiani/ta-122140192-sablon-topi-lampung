<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCancellationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $reason;
    
    public function __construct(Order $order, string $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Pesanan Anda Dibatalkan')
            ->markdown('emails.orders.cancelled', [
                'order' => $this->order,
                'reason' => $this->reason
            ]);
    }
}