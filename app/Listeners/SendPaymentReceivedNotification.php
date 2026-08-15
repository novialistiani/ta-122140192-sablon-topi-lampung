<?php

namespace App\Listeners;

use App\Events\PaymentReceivedEvent;
use App\Models\Order;
use App\Models\CustomDesignOrder;
use App\Services\NotificationService;

class SendPaymentReceivedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentReceivedEvent $event): void
    {
        $order = $event->order;
        $orderType = $event->orderType;
        
        $orderNumber = $this->getOrderNumber($order, $orderType);
        $actionUrl = $this->getOrderUrl($order, $orderType);

        $this->notificationService->send(
            'payment_received',
            $order->user,
            [
                'order_number' => $orderNumber,
                'customer_name' => $order->user->name,
                'transaction_number' => $order->id,
                'amount' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'action_url' => $actionUrl,
            ],
            'medium',
            true // send email
        );
    }
    
    private function getOrderNumber($order, string $orderType): string
    {
        if ($orderType === 'regular' && $order->order_number) {
            return $order->order_number;
        }
        
        if ($orderType === 'custom') {
            return "CUSTOM-{$order->id}";
        }
        
        return $order->order_number ?? 'N/A';
    }
    
    private function getOrderUrl($order, string $orderType): string
    {
        if ($orderType === 'regular') {
            return route('order-detail', ['type' => 'regular', 'id' => $order->id]);
        }
        
        if ($orderType === 'custom') {
            return route('order-detail', ['type' => 'custom', 'id' => $order->id]);
        }
        
        return route('order-history');
    }
}
