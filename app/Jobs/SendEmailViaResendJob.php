<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Mail\NotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Resend\Client as ResendClient;

class SendEmailViaResendJob implements ShouldQueue
{
    use Queueable;

    public int $notificationLogId;
    public array $data;
    public string $templateView;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notificationLogId, array $data, string $templateView)
    {
        $this->notificationLogId = $notificationLogId;
        $this->data = $data;
        $this->templateView = $templateView;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = NotificationLog::find($this->notificationLogId);
        
        if (!$log || !$log->notification) {
            Log::error("NotificationLog or Notification not found: {$this->notificationLogId}");
            return;
        }

        try {
            // Send email using Resend API directly to capture message ID
            $mailable = new NotificationMail(
                $log->notification,
                $this->data,
                $this->templateView
            );
            
            // Build email content
            $rendered = $mailable->render();
            
            // Send via Resend API using PHP SDK
            $resend = new ResendClient(config('services.resend.key'));
            $response = $resend->emails->send([
                'from' => config('mail.from.address'),
                'to' => [$log->recipient_email],
                'subject' => $log->subject ?: $log->notification->title,
                'html' => $rendered,
            ]);

            $log->update([
                'message_id' => $response->id ?? null,
                'resend_email_id' => $response->id ?? null, // For webhook tracking
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'resend_response' => $response instanceof \stdClass ? (array)$response : $response,
                ],
            ]);

            Log::info("Email sent via Resend", [
                'log_id' => $log->id,
                'recipient' => $log->recipient_email,
            ]);

        } catch (\Exception $e) {
            $log->incrementRetry();

            if ($log->retry_count >= $this->tries) {
                $log->updateStatus('failed', $e->getMessage());
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $log = NotificationLog::find($this->notificationLogId);
        
        if ($log) {
            $log->updateStatus('failed', $exception->getMessage());
        }
    }
}
