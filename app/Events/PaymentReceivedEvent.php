<?php

namespace App\Events;

use App\Models\Order;
use App\Models\CustomDesignOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order|CustomDesignOrder $order;
    public string $orderType;

    /**
     * Create a new event instance.
     */
    public function __construct(Order|CustomDesignOrder $order, string $orderType = 'regular')
    {
        $this->order = $order;
        $this->orderType = $orderType;
    }
}
