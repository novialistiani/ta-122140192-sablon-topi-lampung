<?php

namespace App\Listeners;

use App\Events\CustomDesignApprovedEvent;
use App\Services\NotificationService;

class SendCustomDesignApprovedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(CustomDesignApprovedEvent $event): void
    {
        $customDesign = $event->customDesignOrder;

        $this->notificationService->send(
            'custom_design_approved',
            $customDesign->user,
            [
                'design_number' => "CUSTOM-{$customDesign->id}",
                'customer_name' => $customDesign->user->name,
                'design_name' => $customDesign->product_name ?? 'Desain Custom',
                'total_amount' => 'Rp ' . number_format($customDesign->total_price, 0, ',', '.'),
                'action_url' => route('order-detail', ['type' => 'custom', 'id' => $customDesign->id]),
            ],
            'high',
            true // send email
        );
    }
}
