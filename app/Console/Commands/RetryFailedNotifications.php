<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Jobs\SendEmailNotificationJob;
use Illuminate\Console\Command;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:retry {--limit=10 : Maximum number of notifications to retry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed notification emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');

        $this->info("Retrying failed notifications (max: {$limit})...");

        // Get failed notifications with retry count < 3
        $failedLogs = NotificationLog::where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->where('channel', 'email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($failedLogs->isEmpty()) {
            $this->info('No failed notifications to retry.');
            return Command::SUCCESS;
        }

        $this->info("Found {$failedLogs->count()} failed notifications to retry");

        $retried = 0;
        foreach ($failedLogs as $log) {
            try {
                // Reset status to pending
                $log->update([
                    'status' => 'pending',
                    'error_message' => null,
                ]);

                // Get notification data
                $data = $log->notification ? $log->notification->data : [];

                // Dispatch job again
                SendEmailNotificationJob::dispatch($log->id, $data);

                $retried++;

                $this->line("✓ Retrying notification log #{$log->id}");

            } catch (\Exception $e) {
                $this->error("✗ Failed to retry log #{$log->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully queued {$retried} notifications for retry");

        return Command::SUCCESS;
    }
}
