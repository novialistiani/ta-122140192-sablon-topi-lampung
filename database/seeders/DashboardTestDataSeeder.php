<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;

class DashboardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users with verified emails
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $userEmail = "dashboard_test_$i@test.com";
            // Check if user exists
            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                $user = User::create([
                    'name' => "Dashboard Test Customer $i",
                    'email' => $userEmail,
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                    'phone' => '08123456789',
                ]);
            }
            $users[] = $user;
        }

        // Create products with variants
        $productNames = ['TESTING 1', 'TESTING 2', 'TESTING 3', 'TESTING 5'];
        $products = [];
        
        foreach ($productNames as $index => $name) {
            // Check if product already exists
            $product = Product::where('name', $name)->first();
            if (!$product) {
                $product = Product::create([
                    'name' => $name,
                    'description' => "Test product $name for dashboard testing",
                    'category' => 'Kaos',
                    'price' => rand(50000, 150000),
                    'is_active' => true,
                ]);
            }
            $products[$index] = $product;

            // Create variants only if product is new
            if ($product->variants()->count() === 0) {
                // Create variants
                $variantCount = [4, 4, 2, 3][$index] ?? 3;
                for ($v = 1; $v <= $variantCount; $v++) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color' => ['Red', 'Blue', 'Green', 'Yellow'][$v - 1] ?? 'Black',
                        'size' => ['S', 'M', 'L', 'XL'][$v - 1] ?? 'M',
                        'stock' => rand(10, 50),
                        'price' => rand(50000, 150000),
                    ]);
                }
            }
        }

        // Create test orders with various statuses
        $statuses = ['completed', 'pending', 'processing', 'cancelled'];
        $orderCount = 0;
        
        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $product = $products[rand(0, count($products) - 1)];
                $variant = $product->variants()->first();
                $total = rand(100000, 500000);
                
                Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(++$orderCount, 4, '0', STR_PAD_LEFT),
                    'subtotal' => $total,
                    'shipping_cost' => 0,
                    'discount' => 0,
                    'total' => $total,
                    'status' => $statuses[rand(0, count($statuses) - 1)],
                    'items' => json_encode([
                        [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'name' => $product->name,
                            'color' => $variant->color,
                            'size' => $variant->size,
                            'quantity' => rand(1, 5),
                            'price' => $variant->price,
                        ]
                    ]),
                    'created_at' => now()->subDays(rand(0, 180)),
                    'completed_at' => now()->subDays(rand(0, 180)),
                ]);
            }
        }

        $this->command->info('Test data seeded successfully!');
    }
}
