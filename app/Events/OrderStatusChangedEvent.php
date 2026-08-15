<?php

namespace App\Events;

use App\Models\Order;
use App\Models\CustomDesignOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order|CustomDesignOrder $order;
    public string $orderType;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Order|CustomDesignOrder $order, string $orderType = 'regular', string $oldStatus = '', string $newStatus = '')
    {
        $this->order = $order;
        $this->orderType = $orderType;
        $this->oldStatus = $oldStatus ?: $order->status;
        $this->newStatus = $newStatus ?: $order->status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.analytics'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_type' => $this->orderType,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_at' => now()->toIso8601String(),
        ];
    }
}
