<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed admin first
        $this->call(AdminSeeder::class);

        // User::factory(10)->create();

        // Create test user only if not exists
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        } else {
            $this->command->info('Test user already exists!');
        }

        // Run analytics seeder for dashboard testing
        $this->call(CompletedOrdersAnalyticsSeeder::class);
        
        // Run notification template seeder
        $this->call(NotificationTemplateSeeder::class);
    }
}
