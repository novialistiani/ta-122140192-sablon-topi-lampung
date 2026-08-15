<?php

namespace App\Listeners;

use App\Events\OrderRejectedEvent;
use App\Services\NotificationService;

class SendOrderRejectedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderRejectedEvent $event): void
    {
        $order = $event->order;

        $this->notificationService->send(
            'order_rejected',
            $order->user,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'rejection_reason' => $event->reason ?? $order->admin_notes ?? 'Pesanan tidak dapat diproses.',
                'action_url' => route('order-detail', ['type' => 'regular', 'id' => $order->id]),
            ],
            'high',
            true // send email
        );
    }
}
