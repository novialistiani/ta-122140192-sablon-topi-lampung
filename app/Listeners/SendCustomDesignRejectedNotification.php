<?php

namespace App\Listeners;

use App\Events\CustomDesignRejectedEvent;
use App\Services\NotificationService;

class SendCustomDesignRejectedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(CustomDesignRejectedEvent $event): void
    {
        $customDesign = $event->customDesignOrder;

        $this->notificationService->send(
            'custom_design_rejected',
            $customDesign->user,
            [
                'design_number' => "CUSTOM-{$customDesign->id}",
                'customer_name' => $customDesign->user->name,
                'design_name' => $customDesign->product_name ?? 'Desain Custom',
                'rejection_reason' => $event->reason ?? $customDesign->admin_notes ?? 'Pesanan tidak dapat diproses.',
                'action_url' => route('order-detail', ['type' => 'custom', 'id' => $customDesign->id]),
            ],
            'high',
            true // send email
        );
    }
}
