<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * This command is deprecated and now just calls va:check-expired for backward compatibility.
 * All VA expiration logic has been consolidated into CheckExpiredVirtualAccounts command.
 * 
 * @deprecated Use va:check-expired instead
 */
class ExpireVirtualAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'va:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[DEPRECATED] Alias for va:check-expired - Expire virtual accounts yang sudah melewati batas waktu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠ This command (va:expire) is deprecated. Use va:check-expired instead.');
        $this->info('Redirecting to va:check-expired...');
        $this->newLine();
        
        // Call the main consolidated command
        return $this->call('va:check-expired');
    }
}
