<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Services\NotificationService;

class SendOrderCreatedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        $order = $event->order;

        // Send notification to customer
        $this->notificationService->send(
            'order_created',
            $order->user,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'total_amount' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'order_date' => $order->created_at->format('d M Y, H:i'),
                'action_url' => route('order-detail', ['type' => 'regular', 'id' => $order->id]),
            ],
            'medium',
            true // send email
        );

        // Send notification to all active admins
        $admins = \App\Models\Admin::where('status', 'active')->get();
        $this->notificationService->sendToMany(
            'new_order_admin',
            $admins,
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'customer_phone' => $order->user->phone ?? '-',
                'total_amount' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'order_date' => $order->created_at->format('d M Y, H:i'),
                'action_url' => route('admin.order.detail', ['id' => $order->id, 'type' => 'regular']),
            ],
            'high',
            true // send email to admin
        );
    }
}
