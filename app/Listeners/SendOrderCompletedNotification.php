<?php

namespace App\Listeners;

use App\Events\OrderCompletedEvent;
use App\Services\NotificationService;

class SendOrderCompletedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCompletedEvent $event): void
    {
        $order = $event->order;

        $this->notificationService->send(
            'order_completed',
            $order->user,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'total_amount' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'action_url' => route('order-detail', ['type' => 'regular', 'id' => $order->id]),
            ],
            'medium',
            true // send email
        );
    }
}
