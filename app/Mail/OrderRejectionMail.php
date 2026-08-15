<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\CustomDesignOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $rejectionReason;
    public $orderType;
    
    /**
     * Create a new message instance.
     * 
     * @param Order|CustomDesignOrder $order
     * @param string|null $rejectionReason
     * @param string $orderType
     */
    public function __construct($order, ?string $rejectionReason = null, string $orderType = 'regular')
    {
        $this->order = $order;
        $this->rejectionReason = $rejectionReason;
        $this->orderType = $orderType;
    }

    public function build()
    {
        $subject = $this->orderType === 'custom' 
            ? 'Design Custom Anda Tidak Dapat Diproses' 
            : 'Pesanan Anda Tidak Dapat Diproses';
            
        return $this->subject($subject)
            ->markdown('emails.orders.rejected', [
                'order' => $this->order,
                'rejectionReason' => $this->rejectionReason ?? 'Mohon maaf, pesanan Anda tidak dapat diproses saat ini.',
                'orderType' => $this->orderType
            ]);
    }
}