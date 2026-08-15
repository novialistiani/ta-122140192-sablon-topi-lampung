<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\CompletedOrdersAnalyticsSeeder;

class SeedAnalyticsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:analytics {--fresh : Reset database first}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Seed completed orders data for analytics dashboard testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Resetting database...');
            $this->call('migrate:refresh', ['--seed' => true]);
            return Command::SUCCESS;
        }

        $this->info('Seeding analytics test data...');
        $this->call('db:seed', ['--class' => CompletedOrdersAnalyticsSeeder::class]);
        
        $this->info('');
        $this->info('✓ Analytics data seeded successfully!');
        $this->info('✓ Visit http://127.0.0.1:8000/admin/analytic to see the data');
        $this->info('✓ Expected metrics:');
        $this->info('  - Total orders: 20');
        $this->info('  - Total products sold: 29');
        $this->info('  - Revenue: ~2,200,000 - 3,500,000 Rp (varies with random prices)');

        return Command::SUCCESS;
    }
}
