<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CancelExpiredOrders::class,
        Commands\CheckExpiredVirtualAccounts::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check every minute for expired orders
        $schedule->command('orders:cancel-expired')->everyMinute();
        
        // Check every minute for expired Virtual Accounts
        $schedule->command('va:check-expired')->everyMinute();

        // Auto-cancel unconfirmed orders every hour (every 1 jam cek pesanan yang belum dikonfirmasi admin)
        $schedule->command('orders:auto-cancel-unconfirmed')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}