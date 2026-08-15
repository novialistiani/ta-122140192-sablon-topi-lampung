<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\CustomDesignOrder;
use App\Models\CustomDesignUpload;
use App\Models\VirtualAccount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PaymentTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PaidOrdersViaVASeeder extends Seeder
{
    public function run(): void
    {
        // Create sample products if none exist
        if (Product::count() === 0) {
            Product::create([
                'name' => 'Topi Sablon Premium',
                'category' => 'Topi',
                'description' => 'Topi berkualitas untuk sablon custom',
                'price' => 75000,
                'image' => 'default.jpg',
            ]);
            Product::create([
                'name' => 'Kaos Sablon Custom',
                'category' => 'Kaos',
                'description' => 'Kaos berkualitas untuk sablon custom',
                'price' => 50000,
                'image' => 'default.jpg',
            ]);
        }

        // CUSTOMER 1: Budi Santoso
        $customer1 = User::firstOrCreate(
            ['email' => 'budi.santoso@email.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'phone' => '081234567890',
                'email_verified_at' => now(),
            ]
        );

        $products = Product::inRandomOrder()->limit(2)->get();
        $product1 = $products->first();
        $product2 = $products->count() > 1 ? $products->last() : $products->first();

        $variant1 = $product1->variants()->first() ?? ProductVariant::create([
            'product_id' => $product1->id,
            'color' => 'Merah',
            'size' => 'L',
            'stock' => 50,
            'price' => 75000,
            'image' => $product1->image,
        ]);

        $regularOrder1 = Order::create([
            'user_id' => $customer1->id,
            'order_number' => \App\Models\OrderNumberSequence::getNextOrderNumber(),
            'items' => [
                [
                    'product_id' => $product1->id,
                    'variant_id' => $variant1->id,
                    'name' => $product1->name,
                    'color' => $variant1->color,
                    'size' => $variant1->size,
                    'quantity' => 2,
                    'price' => 75000,
                    'image' => $product1->image,
                ],
                [
                    'product_id' => $product2->id,
                    'variant_id' => null,
                    'name' => $product2->name,
                    'quantity' => 1,
                    'price' => 50000,
                    'image' => $product2->image,
                ],
            ],
            'subtotal' => 200000,
            'shipping_cost' => 25000,
            'discount' => 0,
            'total' => 225000,
            'status' => 'processing',
            'payment_status' => 'paid',
            'approved_at' => now()->subDays(15),
            'paid_at' => now()->subDays(14),
            'processing_at' => now()->subDays(13),
            'payment_deadline' => now()->subDays(14)->addHours(24),
        ]);

        $va1 = VirtualAccount::create([
            'user_id' => $customer1->id,
            'order_type' => 'regular',
            'order_id' => $regularOrder1->id,
            'bank_code' => 'BCA',
            'va_number' => '62100' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'amount' => 225000,
            'status' => 'paid',
            'expired_at' => now()->subDays(14)->addHours(24),
            'paid_at' => now()->subDays(14),
        ]);

        PaymentTransaction::create([
            'transaction_id' => 'TRX-' . time() . '-BUDI-001',
            'user_id' => $customer1->id,
            'virtual_account_id' => $va1->id,
            'order_type' => 'regular',
            'order_id' => $regularOrder1->id,
            'payment_method' => 'virtual_account',
            'payment_channel' => 'bca',
            'amount' => 225000,
            'status' => 'paid',
            'paid_at' => now()->subDays(14),
            'notes' => 'Pembayaran sukses via BCA Virtual Account',
        ]);

        $customProduct1 = Product::whereHas('variants')->inRandomOrder()->first() ?? $product1;
        $customVariant1 = $customProduct1->variants()->first();

        $customOrder1 = CustomDesignOrder::create([
            'user_id' => $customer1->id,
            'product_id' => $customProduct1->id,
            'variant_id' => $customVariant1->id,
            'product_name' => $customProduct1->name,
            'product_price' => $customVariant1->price ?? 50000,
            'quantity' => 1,
            'cutting_type' => 'Cutting PVC Flex',
            'special_materials' => ['hologram', 'gloss'],
            'additional_description' => 'Gradasi warna halus, huruf tebal 5mm',
            'status' => 'processing',
            'payment_status' => 'paid',
            'total_price' => 150000,
            'approved_at' => now()->subDays(20),
        ]);

        // Create sample upload for custom order 1
        $this->createSampleDesignUpload($customOrder1, 'Budi_Custom_Design_1.jpg');

        $va1Custom = VirtualAccount::create([
            'user_id' => $customer1->id,
            'order_type' => 'custom',
            'order_id' => $customOrder1->id,
            'bank_code' => 'BNI',
            'va_number' => '72100' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'amount' => 150000,
            'status' => 'paid',
            'expired_at' => now()->subDays(20)->addHours(24),
            'paid_at' => now()->subDays(19),
        ]);

        PaymentTransaction::create([
            'transaction_id' => 'TRX-' . time() . '-BUDI-002',
            'user_id' => $customer1->id,
            'virtual_account_id' => $va1Custom->id,
            'order_type' => 'custom',
            'order_id' => $customOrder1->id,
            'payment_method' => 'virtual_account',
            'payment_channel' => 'bni',
            'amount' => 150000,
            'status' => 'paid',
            'paid_at' => now()->subDays(19),
            'notes' => 'Pembayaran sukses via BNI Virtual Account',
        ]);

        // CUSTOMER 2: Siti Nurhaliza
        $customer2 = User::firstOrCreate(
            ['email' => 'siti.nurhaliza@email.com'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password123'),
                'phone' => '082345678901',
                'email_verified_at' => now(),
            ]
        );

        $products2 = Product::inRandomOrder()->limit(3)->get();
        $items2 = [];
        $total2 = 0;

        foreach ($products2 as $idx => $product) {
            $variant = $product->variants()->first() ?? ProductVariant::create([
                'product_id' => $product->id,
                'color' => 'Biru',
                'size' => 'M',
                'stock' => 100,
                'price' => 85000,
                'image' => $product->image,
            ]);
            
            $price = $variant->price ?? 85000;
            $qty = $idx === 0 ? 3 : 1;
            $items2[] = [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'name' => $product->name,
                'color' => $variant->color,
                'size' => $variant->size,
                'quantity' => $qty,
                'price' => $price,
                'image' => $product->image,
            ];
            $total2 += $price * $qty;
        }

        $regularOrder2 = Order::create([
            'user_id' => $customer2->id,
            'order_number' => \App\Models\OrderNumberSequence::getNextOrderNumber(),
            'items' => $items2,
            'subtotal' => $total2,
            'shipping_cost' => 30000,
            'discount' => 15000,
            'total' => $total2 + 30000 - 15000,
            'status' => 'processing',
            'payment_status' => 'paid',
            'approved_at' => now()->subDays(10),
            'paid_at' => now()->subDays(9),
            'processing_at' => now()->subDays(8),
            'payment_deadline' => now()->subDays(9)->addHours(24),
        ]);

        $totalAmount2 = $total2 + 30000 - 15000;

        $va2 = VirtualAccount::create([
            'user_id' => $customer2->id,
            'order_type' => 'regular',
            'order_id' => $regularOrder2->id,
            'bank_code' => 'Mandiri',
            'va_number' => '89100' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'amount' => $totalAmount2,
            'status' => 'paid',
            'expired_at' => now()->subDays(9)->addHours(24),
            'paid_at' => now()->subDays(9),
        ]);

        PaymentTransaction::create([
            'transaction_id' => 'TRX-' . time() . '-SITI-001',
            'user_id' => $customer2->id,
            'virtual_account_id' => $va2->id,
            'order_type' => 'regular',
            'order_id' => $regularOrder2->id,
            'payment_method' => 'virtual_account',
            'payment_channel' => 'mandiri',
            'amount' => $totalAmount2,
            'status' => 'paid',
            'paid_at' => now()->subDays(9),
            'notes' => 'Pembayaran sukses via Mandiri Virtual Account',
        ]);

        // CUSTOMER 3: Ahmad Wijaya
        $customer3 = User::firstOrCreate(
            ['email' => 'ahmad.wijaya@email.com'],
            [
                'name' => 'Ahmad Wijaya',
                'password' => Hash::make('password123'),
                'phone' => '083456789012',
                'email_verified_at' => now(),
            ]
        );

        $customProduct3 = Product::whereHas('variants')->inRandomOrder()->first() ?? Product::inRandomOrder()->first();
        $customVariant3 = $customProduct3->variants()->first() ?? ProductVariant::create([
            'product_id' => $customProduct3->id,
            'color' => 'Kuning',
            'size' => 'XL',
            'stock' => 30,
            'price' => 120000,
            'image' => $customProduct3->image,
        ]);

        $customOrder3 = CustomDesignOrder::create([
            'user_id' => $customer3->id,
            'product_id' => $customProduct3->id,
            'variant_id' => $customVariant3->id,
            'product_name' => $customProduct3->name,
            'product_price' => $customVariant3->price ?? 120000,
            'quantity' => 5,
            'cutting_type' => 'Cutting Polymer',
            'special_materials' => ['glitter', 'emboss'],
            'additional_description' => 'Design 3D dengan efek emboss premium, pilihan warna custom',
            'status' => 'processing',
            'payment_status' => 'paid',
            'total_price' => 550000,
            'approved_at' => now()->subDays(7),
        ]);

        // Create sample upload for custom order 3
        $this->createSampleDesignUpload($customOrder3, 'Ahmad_Custom_Design_1.jpg');

        $va3 = VirtualAccount::create([
            'user_id' => $customer3->id,
            'order_type' => 'custom',
            'order_id' => $customOrder3->id,
            'bank_code' => 'BRI',
            'va_number' => '92100' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'amount' => 550000,
            'status' => 'paid',
            'expired_at' => now()->subDays(7)->addHours(24),
            'paid_at' => now()->subDays(6),
        ]);

        PaymentTransaction::create([
            'transaction_id' => 'TRX-' . time() . '-AHMAD-001',
            'user_id' => $customer3->id,
            'virtual_account_id' => $va3->id,
            'order_type' => 'custom',
            'order_id' => $customOrder3->id,
            'payment_method' => 'virtual_account',
            'payment_channel' => 'bri',
            'amount' => 550000,
            'status' => 'paid',
            'paid_at' => now()->subDays(6),
            'notes' => 'Pembayaran sukses via BRI Virtual Account',
        ]);

        $this->command->info('✓ Seeder berhasil! 3 customer dengan pesanan PAID via VA Bank sudah dibuat.');
    }

    /**
     * Create sample design upload for custom order
     */
    private function createSampleDesignUpload($customOrder, $filename)
    {
        // Create uploads directory if it doesn't exist
        $uploadsDir = storage_path('app/custom-designs/' . $customOrder->id);
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Create a simple placeholder image (1x1 transparent PNG)
        $placeholderImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        
        $filePath = $uploadsDir . '/' . time() . '_' . $filename;
        file_put_contents($filePath, $placeholderImage);

        // Create upload record
        CustomDesignUpload::create([
            'custom_design_order_id' => $customOrder->id,
            'section_name' => 'design',
            'file_path' => 'custom-designs/' . $customOrder->id . '/' . basename($filePath),
            'file_name' => $filename,
            'file_size' => strlen($placeholderImage),
        ]);
    }
}

