<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\CustomerAddress;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CompletedOrdersAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates completed orders with paid status to test analytics dashboard
     */
    public function run(): void
    {
        // Check if analytics data already exists
        $existingUser = User::where('email', 'analytics_customer_1@test.com')->first();
        if ($existingUser) {
            $this->command->warn('⚠ Analytics data already exists. Skipping...');
            $this->command->info('To reseed, run: php artisan migrate:refresh --seed');
            return;
        }

        // Create test users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $userEmail = "analytics_customer_$i@test.com";
            $user = User::where('email', $userEmail)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => "Analytics Customer $i",
                    'email' => $userEmail,
                    'email_verified_at' => now(),
                    'password' => bcrypt('password123'),
                    'phone' => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                ]);
                
                // Create customer address for each user
                CustomerAddress::create([
                    'user_id' => $user->id,
                    'label' => 'Home',
                    'recipient_name' => $user->name,
                    'phone' => $user->phone,
                    'province' => 'Lampung',
                    'city' => 'Bandar Lampung',
                    'postal_code' => '35000',
                    'address' => "Jalan Test $i, Bandar Lampung",
                    'is_primary' => true,
                ]);
            }
            $users[] = $user;
        }

        // Get or create products
        $productData = [
            ['name' => 'Kaos Premium Basic', 'price' => 85000, 'category' => 'Kaos', 'image' => 'https://i.pinimg.com/1200x/3e/6b/f5/3e6bf5378b6ae4d43263dfb626d37588.jpg'],
            ['name' => 'Kaos Custom Design', 'price' => 120000, 'category' => 'Kaos', 'image' => 'https://i.pinimg.com/736x/45/4c/92/454c92dc87e9774bed336c9ea9d132ed.jpg'],
            ['name' => 'Kaos Couple Pack', 'price' => 150000, 'category' => 'Kaos', 'image' => 'https://i.pinimg.com/736x/4f/7e/bf/4f7ebfdc234afefe71d5f4a8ec8ba408.jpg'],
            ['name' => 'Jersey Sport', 'price' => 95000, 'category' => 'Jersey', 'image' => 'https://i.pinimg.com/1200x/e1/4f/84/e14f84017368f18fba24c18f4da36fef.jpg'],
            ['name' => 'Polo Shirt Premium', 'price' => 110000, 'category' => 'Polo', 'image' => 'https://i.pinimg.com/1200x/fd/20/e2/fd20e257b019babc8014be0dea3766c6.jpg'],
        ];

        $products = [];
        foreach ($productData as $data) {
            $product = Product::where('name', $data['name'])->first();
            
            if (!$product) {
                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => "Premium quality " . $data['name'],
                    'category' => $data['category'],
                    'price' => $data['price'],
                    'original_price' => $data['price'],
                    'image' => $data['image'],
                    'stock' => 100,
                    'is_active' => true,
                    'custom_design_allowed' => true,
                ]);

                // Create variants for each product
                $colors = ['Red', 'Blue', 'Black', 'White', 'Yellow'];
                $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                
                foreach ($colors as $colorIdx => $color) {
                    if ($colorIdx < 3) { // Create 3 color variants for each product
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'color' => $color,
                            'size' => $sizes[$colorIdx],
                            'stock' => 50,
                            'price' => $data['price'] + rand(-10000, 20000),
                        ]);
                    }
                }
            }
            $products[] = $product;
        }

        // Create completed orders for analytics testing
        $orderCounter = 1;
        
        // Order scenarios to test analytics:
        // 1. Multiple orders from one user
        // 2. Orders from different days (to test trend data)
        // 3. Various quantities and prices
        
        $scenarios = [
            // User 1: Multiple orders over time
            ['user' => 0, 'quantity' => 2, 'product' => 0, 'days_ago' => 30, 'count' => 3],
            ['user' => 0, 'quantity' => 1, 'product' => 1, 'days_ago' => 20, 'count' => 2],
            ['user' => 0, 'quantity' => 3, 'product' => 2, 'days_ago' => 10, 'count' => 1],
            
            // User 2: Regular buyer
            ['user' => 1, 'quantity' => 1, 'product' => 0, 'days_ago' => 25, 'count' => 2],
            ['user' => 1, 'quantity' => 2, 'product' => 3, 'days_ago' => 15, 'count' => 1],
            ['user' => 1, 'quantity' => 1, 'product' => 4, 'days_ago' => 5, 'count' => 2],
            
            // User 3: Bulk buyer
            ['user' => 2, 'quantity' => 5, 'product' => 0, 'days_ago' => 28, 'count' => 1],
            ['user' => 2, 'quantity' => 3, 'product' => 1, 'days_ago' => 18, 'count' => 1],
            
            // User 4: Frequent small orders
            ['user' => 3, 'quantity' => 1, 'product' => 2, 'days_ago' => 22, 'count' => 3],
            ['user' => 3, 'quantity' => 1, 'product' => 3, 'days_ago' => 12, 'count' => 2],
            
            // User 5: Recent buyer
            ['user' => 4, 'quantity' => 2, 'product' => 4, 'days_ago' => 3, 'count' => 1],
            ['user' => 4, 'quantity' => 1, 'product' => 0, 'days_ago' => 2, 'count' => 2],
        ];

        foreach ($scenarios as $scenario) {
            $user = $users[$scenario['user']];
            $product = $products[$scenario['product']];
            $variant = $product->variants()->first();
            
            for ($i = 0; $i < $scenario['count']; $i++) {
                $quantity = $scenario['quantity'];
                $unitPrice = $product->price;
                $subtotal = $quantity * $unitPrice;
                $shippingCost = rand(10000, 25000);
                $discount = rand(0, 15000);
                $total = $subtotal + $shippingCost - $discount;

                // Create order with completed and paid status
                $completedDate = Carbon::now()->subDays($scenario['days_ago'] + $i);
                
                Order::create([
                    'user_id' => $user->id,
                    'customer_address_id' => $user->addresses()->first()->id ?? null,
                    'order_number' => 'ORD-' . $completedDate->format('Ymd') . '-' . str_pad($orderCounter++, 5, '0', STR_PAD_LEFT),
                    'items' => json_encode([
                        [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'name' => $product->name,
                            'color' => $variant->color,
                            'size' => $variant->size,
                            'quantity' => $quantity,
                            'price' => $unitPrice,
                        ]
                    ]),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'completed', // Mark as completed
                    'payment_status' => 'paid', // Mark as paid
                    'shipping_service' => 'JNE',
                    'paid_at' => $completedDate->copy()->addHours(2),
                    'completed_at' => $completedDate->copy()->addDays(3),
                    'created_at' => $completedDate,
                    'updated_at' => $completedDate->copy()->addDays(3),
                ]);
            }
        }

        $this->command->info('✓ Completed orders analytics seeder ran successfully!');
        $this->command->info("✓ Created " . count($users) . " test customers with paid orders");
        $this->command->info("✓ Expected products sold: 29");
        $this->command->info("✓ Total revenue should be visible in analytics dashboard");
    }
}
