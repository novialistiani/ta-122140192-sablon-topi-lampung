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

class OrderListTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates orders with ALL statuses for order-list testing
     */
    public function run(): void
    {
        // Check if data already exists
        $existingOrder = Order::where('order_number', 'like', 'TEST-ORDER-%')->first();
        if ($existingOrder) {
            $this->command->warn('⚠ Order test data already exists. Skipping...');
            return;
        }

        // Create test users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $userEmail = "order_test_$i@test.com";
            $user = User::where('email', $userEmail)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => "Order Test Customer $i",
                    'email' => $userEmail,
                    'email_verified_at' => now(),
                    'password' => bcrypt('password123'),
                    'phone' => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                ]);
                
                // Create customer address
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
            ['name' => 'Kaos Premium', 'price' => 85000],
            ['name' => 'Jersey Sport', 'price' => 95000],
            ['name' => 'Polo Shirt', 'price' => 110000],
            ['name' => 'Jaket Casual', 'price' => 150000],
            ['name' => 'Topi Sablon', 'price' => 45000],
        ];

        $products = [];
        foreach ($productData as $data) {
            $product = Product::where('name', $data['name'])->first();
            
            if (!$product) {
                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => "Test " . $data['name'],
                    'category' => 'Test',
                    'price' => $data['price'],
                    'original_price' => $data['price'],
                    'stock' => 100,
                    'is_active' => true,
                ]);

                // Create variants
                $colors = ['Red', 'Blue', 'Black'];
                $sizes = ['S', 'M', 'L'];
                
                foreach ($colors as $idx => $color) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'color' => $color,
                        'size' => $sizes[$idx],
                        'stock' => 50,
                        'price' => $data['price'],
                    ]);
                }
            }
            $products[] = $product;
        }

        // All possible order statuses
        $statuses = ['pending', 'processing', 'approved', 'rejected', 'completed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid'];
        
        $orderCounter = 1;

        // Create orders for each status combination
        foreach ($statuses as $statusIdx => $status) {
            $user = $users[$statusIdx % count($users)];
            $product = $products[$statusIdx % count($products)];
            $variant = $product->variants()->first();
            
            $quantity = rand(1, 3);
            $unitPrice = $product->price;
            $subtotal = $quantity * $unitPrice;
            $shippingCost = rand(10000, 25000);
            $discount = rand(0, 5000);
            $total = $subtotal + $shippingCost - $discount;

            // Payment status depends on order status
            $paymentStatus = in_array($status, ['completed', 'processing']) ? 'paid' : 'unpaid';
            
            $createdDate = now()->subDays(rand(1, 30));
            
            Order::create([
                'user_id' => $user->id,
                'customer_address_id' => $user->addresses()->first()->id,
                'order_number' => 'TEST-ORDER-' . $status . '-' . str_pad($orderCounter++, 5, '0', STR_PAD_LEFT),
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
                'status' => $status,
                'payment_status' => $paymentStatus,
                'shipping_service' => 'JNE',
                'paid_at' => $paymentStatus === 'paid' ? $createdDate->copy()->addHours(2) : null,
                'completed_at' => $status === 'completed' ? $createdDate->copy()->addDays(3) : null,
                'created_at' => $createdDate,
                'updated_at' => $createdDate,
            ]);
        }

        // Create additional orders with various combinations
        $combinations = [
            ['status' => 'pending', 'payment_status' => 'unpaid'],
            ['status' => 'pending', 'payment_status' => 'paid'],
            ['status' => 'processing', 'payment_status' => 'paid'],
            ['status' => 'completed', 'payment_status' => 'paid'],
            ['status' => 'cancelled', 'payment_status' => 'unpaid'],
            ['status' => 'rejected', 'payment_status' => 'unpaid'],
        ];

        foreach ($combinations as $combo) {
            for ($i = 0; $i < 2; $i++) {
                $user = $users[rand(0, count($users) - 1)];
                $product = $products[rand(0, count($products) - 1)];
                $variant = $product->variants()->first();
                
                $quantity = rand(1, 5);
                $subtotal = $quantity * $product->price;
                $shippingCost = rand(10000, 25000);
                $discount = rand(0, 10000);
                $total = $subtotal + $shippingCost - $discount;
                
                $createdDate = now()->subDays(rand(1, 60));
                
                Order::create([
                    'user_id' => $user->id,
                    'customer_address_id' => $user->addresses()->first()->id,
                    'order_number' => 'TEST-ORDER-' . str_pad($orderCounter++, 6, '0', STR_PAD_LEFT),
                    'items' => json_encode([
                        [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'name' => $product->name,
                            'color' => $variant->color,
                            'size' => $variant->size,
                            'quantity' => $quantity,
                            'price' => $product->price,
                        ]
                    ]),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => $combo['status'],
                    'payment_status' => $combo['payment_status'],
                    'shipping_service' => 'JNE',
                    'paid_at' => $combo['payment_status'] === 'paid' ? $createdDate->copy()->addHours(2) : null,
                    'completed_at' => $combo['status'] === 'completed' ? $createdDate->copy()->addDays(3) : null,
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                ]);
            }
        }

        $this->command->info('✅ Order test data created successfully!');
        $this->command->info('✓ Total Orders Created: ' . Order::where('order_number', 'like', 'TEST-ORDER-%')->count());
        $this->command->info('✓ Statuses: pending, processing, approved, rejected, completed, cancelled');
        $this->command->info('✓ Payment Statuses: paid, unpaid');
    }
}
