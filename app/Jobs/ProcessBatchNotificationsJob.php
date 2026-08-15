<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBatchNotificationsJob implements ShouldQueue
{
    use Queueable;

    public string $notificationType;
    public array $recipientIds;
    public string $recipientType;
    public array $data;
    public string $priority;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $notificationType,
        array $recipientIds,
        string $recipientType,
        array $data,
        string $priority = 'medium'
    ) {
        $this->notificationType = $notificationType;
        $this->recipientIds = $recipientIds;
        $this->recipientType = $recipientType;
        $this->data = $data;
        $this->priority = $priority;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            // Get recipients
            $recipients = $this->recipientType::whereIn('id', $this->recipientIds)->get();

            if ($recipients->isEmpty()) {
                Log::warning("No recipients found for batch notification", [
                    'type' => $this->notificationType,
                    'ids' => $this->recipientIds,
                ]);
                return;
            }

            // Send to all recipients
            $notifications = $notificationService->sendToMany(
                $this->notificationType,
                $recipients,
                $this->data,
                $this->priority,
                true
            );

            Log::info("Batch notifications sent", [
                'type' => $this->notificationType,
                'count' => count($notifications),
                'recipients' => count($recipients),
            ]);

        } catch (\Exception $e) {
            Log::error("Batch notification failed", [
                'type' => $this->notificationType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
