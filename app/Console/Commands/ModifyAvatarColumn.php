<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModifyAvatarColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:modify-avatar-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            \DB::statement('ALTER TABLE users MODIFY avatar TEXT;');
            $this->info('Avatar column modified successfully!');
        } catch (\Exception $e) {
            $this->error('Error modifying avatar column: ' . $e->getMessage());
        }
    }
}
