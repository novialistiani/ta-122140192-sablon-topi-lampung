<?php

namespace App\Listeners;

use App\Events\CustomDesignUploadedEvent;
use App\Services\NotificationService;

class SendCustomDesignUploadedNotification
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(CustomDesignUploadedEvent $event): void
    {
        $customDesign = $event->customDesignOrder;

        // Notify customer
        $this->notificationService->send(
            'custom_design_uploaded',
            $customDesign->user,
            [
                'design_number' => "CUSTOM-{$customDesign->id}",
                'customer_name' => $customDesign->user->name,
                'design_name' => $customDesign->product_name ?? 'Desain Custom',
                'action_url' => route('order-detail', ['type' => 'custom', 'id' => $customDesign->id]),
            ],
            'low',
            true // send email
        );

        // Notify all active admins
        $admins = \App\Models\Admin::where('status', 'active')->get();
        $this->notificationService->sendToMany(
            'new_custom_design_admin',
            $admins,
            [
                'design_number' => "CUSTOM-{$customDesign->id}",
                'customer_name' => $customDesign->user->name,
                'design_name' => $customDesign->product_name ?? 'Desain Custom',
                'total_amount' => 'Rp ' . number_format($customDesign->total_price, 0, ',', '.'),
                'action_url' => route('admin.order.detail', ['id' => $customDesign->id, 'type' => 'custom']),
            ],
            'high',
            true // send email to admin
        );
    }
}
