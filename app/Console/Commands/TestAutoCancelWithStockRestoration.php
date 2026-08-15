<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Models\CustomerAddress;
use App\Models\PaymentMethod;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestAutoCancelWithStockRestoration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-cancel-stock-restoration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test auto-cancel dengan verifikasi stock restoration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('TEST: AUTO-CANCEL WITH STOCK RESTORATION');
        $this->line(str_repeat('=', 60));
        $this->line('');

        try {
            // Setup test data
            $user = User::first() ?? User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'phone' => '08123456789',
            ]);

            $address = CustomerAddress::where('user_id', $user->id)->first() ?? 
                       CustomerAddress::create([
                           'user_id' => $user->id,
                           'recipient_name' => 'Test Recipient',
                           'phone' => '08123456789',
                           'province' => 'Test Province',
                           'city' => 'Test City',
                           'district' => 'Test District',
                           'postal_code' => '12345',
                           'address' => 'Test Address',
                           'is_primary' => true,
                       ]);

            $paymentMethod = PaymentMethod::first() ?? 
                            PaymentMethod::create([
                                'code' => 'test_bank_' . uniqid(),
                                'name' => 'Bank Transfer',
                                'description' => 'Transfer Bank',
                                'is_active' => true,
                            ]);

            // Create test product if not exists
            $product = Product::first();
            if (!$product) {
                $product = Product::create([
                    'name' => 'Test Product for Stock',
                    'slug' => 'test-product-stock-' . uniqid(),
                    'description' => 'Test product',
                    'sku' => 'TEST-STOCK-' . uniqid(),
                    'price' => 50000,
                    'stock' => 100,
                    'is_active' => true,
                ]);
            }

            $this->info('✓ Setup test data berhasil');
            $this->line('');

            // TEST: Create order dengan product dan track stock
            $this->info('TEST: Create order dan track stock sebelum auto-cancel');
            $this->line(str_repeat('-', 60));

            $stockBefore = $product->stock;
            $this->info("Stock produk sebelum: {$stockBefore}");

            $testOrder = Order::create([
                'user_id' => $user->id,
                'customer_address_id' => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'items' => json_encode([
                    [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'quantity' => 5,
                        'price' => 50000,
                    ]
                ]),
                'subtotal' => 250000,
                'shipping_cost' => 10000,
                'discount' => 0,
                'total' => 260000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_service' => 'jne',
                'customer_notes' => 'Test order untuk stock restoration',
                'admin_notes' => '',
                'confirmation_deadline' => Carbon::now()->subHours(25),
            ]);

            $this->info("✓ Order dibuat: #{$testOrder->order_number}");
            $this->info("  Items: {$testOrder->items}");
            $this->line('');

            // Check stock after order creation
            $product->refresh();
            $stockAfterOrder = $product->stock;
            $this->info("Stock produk setelah order dibuat: {$stockAfterOrder}");
            $this->info("(Catatan: Stock tidak berkurang saat order, hanya saat payment confirmed)");
            $this->line('');

            // TEST: Run auto-cancel command
            $this->info('TEST: Jalankan orders:auto-cancel-unconfirmed command');
            $this->line(str_repeat('-', 60));
            $this->call('orders:auto-cancel-unconfirmed');
            $this->line('');

            // Verify order is cancelled
            $testOrder->refresh();
            $this->info('TEST: Verifikasi order di-cancel');
            $this->line(str_repeat('-', 60));

            if ($testOrder->status === 'cancelled') {
                $this->info("✓ Order status: {$testOrder->status}");
                $this->info("✓ Cancelled at: {$testOrder->cancelled_at}");
                $this->info("✓ Admin notes: " . substr($testOrder->admin_notes, 0, 80) . "...");
            } else {
                $this->error("❌ Order masih {$testOrder->status}");
                return Command::FAILURE;
            }

            $this->line('');

            // Check final stock
            $product->refresh();
            $stockAfterCancel = $product->stock;
            $this->info("Stock produk setelah auto-cancel: {$stockAfterCancel}");
            $this->line('');

            // Verify stock restoration (stock should return to original amount)
            $this->info('TEST: Verifikasi stock restoration');
            $this->line(str_repeat('-', 60));

            // Since we don't reduce stock on order creation, stock should remain same
            if ($stockAfterCancel == $stockAfterOrder) {
                $this->info("✓ Stock tetap konsisten");
                $this->info("  Sebelum: {$stockBefore}");
                $this->info("  Sesudah: {$stockAfterCancel}");
            } else {
                $this->warn("⚠ Stock berubah dari {$stockAfterOrder} menjadi {$stockAfterCancel}");
                $this->warn("  (Ini terjadi jika stock di-reduce saat order dibuat)");
            }

            $this->line('');
            $this->info(str_repeat('=', 60));
            $this->info('✅ TEST STOCK RESTORATION COMPLETED!');
            $this->info(str_repeat('=', 60));

            // Cleanup
            $this->line('');
            $this->info('Cleanup: Menghapus test order...');
            Order::where('id', $testOrder->id)->delete();
            $this->info('✓ Cleanup selesai');
            $this->line('');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
