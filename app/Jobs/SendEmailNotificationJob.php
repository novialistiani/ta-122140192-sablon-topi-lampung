<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $notificationLogId;
    public array $data;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notificationLogId, array $data)
    {
        $this->notificationLogId = $notificationLogId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = NotificationLog::find($this->notificationLogId);
        
        if (!$log) {
            Log::error("NotificationLog not found: {$this->notificationLogId}");
            return;
        }

        try {
            // Get notification and template
            $notification = $log->notification;
            if (!$notification) {
                Log::error("Notification not found for log: {$this->notificationLogId}");
                $log->updateStatus('failed', 'Notification not found');
                return;
            }

            $template = NotificationTemplate::getByType($notification->type);
            if (!$template) {
                Log::error("Template not found: {$notification->type}");
                $log->updateStatus('failed', 'Template not found');
                return;
            }

            // Send via Laravel Mail (works with any driver: resend, smtp, mailgun, etc.)
            // Wrap data in 'data' key as expected by email templates
            $htmlContent = view($template->template, [
                'data' => array_merge($this->data, [
                    'action_url' => $notification->action_url,
                    'action_text' => $notification->action_text,
                ]),
                'notification' => $notification,
                'actionUrl' => $notification->action_url,
                'actionText' => $notification->action_text,
            ])->render();

            Mail::html($htmlContent, function ($message) use ($log) {
                $message->to($log->recipient_email)
                        ->subject($log->subject);
            });

            // Update log status
            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'driver' => config('mail.default'),
                ],
            ]);

            Log::info("Email sent successfully", [
                'log_id' => $log->id,
                'recipient' => $log->recipient_email,
                'driver' => config('mail.default'),
            ]);

        } catch (\Exception $e) {
            // Increment retry count
            $log->incrementRetry();

            // Check if max retries reached
            if ($log->retry_count >= $this->tries) {
                $log->updateStatus('failed', $e->getMessage());
                
                Log::error("Email failed after {$this->tries} attempts", [
                    'log_id' => $log->id,
                    'error' => $e->getMessage(),
                    'recipient' => $log->recipient_email,
                ]);
            } else {
                // Update status to pending for retry
                $log->update([
                    'status' => 'pending',
                    'error_message' => $e->getMessage(),
                ]);

                Log::warning("Email sending failed, will retry", [
                    'log_id' => $log->id,
                    'retry_count' => $log->retry_count,
                    'error' => $e->getMessage(),
                ]);
            }

            // Re-throw exception untuk trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $log = NotificationLog::find($this->notificationLogId);
        
        if ($log) {
            $log->updateStatus('failed', $exception->getMessage());
            
            Log::error("SendEmailNotificationJob failed permanently", [
                'log_id' => $log->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
