<?php

namespace App\Listeners;

use App\Events\OrderApprovedEvent;
use App\Services\NotificationService;

class SendOrderApprovedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderApprovedEvent $event): void
    {
        $order = $event->order;

        $this->notificationService->send(
            'order_approved',
            $order->user,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'total_amount' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'notes' => $order->admin_notes ?? 'Pesanan Anda telah disetujui.',
                'action_url' => route('order-detail', ['type' => 'regular', 'id' => $order->id]),
            ],
            'high',
            true // send email
        );
    }
}
