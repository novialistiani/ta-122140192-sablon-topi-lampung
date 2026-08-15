<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Update or create admin with super admin role
        Admin::updateOrCreate(
            ['email' => 'sablontopilampung@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('sablontopi@2025#'),
                'role' => 'super_admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin berhasil dibuat!');
        $this->command->info('Email: sablontopilampung@gmail.com');
        $this->command->info('Password: sablontopi@2025#');
    }
}
