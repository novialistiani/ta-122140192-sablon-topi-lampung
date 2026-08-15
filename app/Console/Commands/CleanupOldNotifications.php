<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=30 : Days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old read notifications and logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $this->info("Cleaning up notifications older than {$days} days...");

        DB::beginTransaction();

        try {
            // Delete old read notifications
            $deletedNotifications = Notification::whereNotNull('read_at')
                ->where('read_at', '<', $date)
                ->delete();

            $this->info("Deleted {$deletedNotifications} old read notifications");

            // Delete old archived notifications
            $deletedArchived = Notification::whereNotNull('archived_at')
                ->where('archived_at', '<', $date)
                ->delete();

            $this->info("Deleted {$deletedArchived} archived notifications");

            // Delete old successful notification logs
            $deletedLogs = NotificationLog::whereIn('status', ['delivered', 'opened', 'clicked'])
                ->where('created_at', '<', $date)
                ->delete();

            $this->info("Deleted {$deletedLogs} old notification logs");

            DB::commit();

            $this->info('Cleanup completed successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Cleanup failed: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }
}
