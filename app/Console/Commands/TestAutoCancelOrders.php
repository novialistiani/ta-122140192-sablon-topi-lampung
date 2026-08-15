<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Models\CustomerAddress;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestAutoCancelOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-cancel-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test fitur auto-cancel orders yang belum dikonfirmasi admin dalam 24 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('TEST: AUTO-CANCEL UNCONFIRMED ORDERS');
        $this->line(str_repeat('=', 60));
        $this->line('');

        try {
            // Create test user jika belum ada
            $user = User::first();
            if (!$user) {
                $user = User::create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                    'phone' => '08123456789',
                ]);
                $this->info('✓ Test user dibuat');
            }

            // Create test address jika belum ada
            $address = CustomerAddress::where('user_id', $user->id)->first();
            if (!$address) {
                $address = CustomerAddress::create([
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
                $this->info('✓ Test address dibuat');
            }

            // Create test payment method jika belum ada
            $paymentMethod = PaymentMethod::first();
            if (!$paymentMethod) {
                $paymentMethod = PaymentMethod::create([
                    'code' => 'test_bank',
                    'name' => 'Bank Transfer',
                    'description' => 'Transfer Bank',
                    'is_active' => true,
                ]);
                $this->info('✓ Test payment method dibuat');
            }

            $this->info('✓ Setup test data berhasil');
            $this->line('');

            // TEST 1: Create order dengan confirmation_deadline sudah terlewat
            $this->info('TEST 1: Create order dengan confirmation_deadline sudah terlewat');
            $this->line(str_repeat('-', 60));

            $testOrder = Order::create([
                'user_id' => $user->id,
                'customer_address_id' => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'items' => json_encode([
                    [
                        'product_id' => 1,
                        'name' => 'Test Product',
                        'quantity' => 1,
                        'price' => 50000,
                    ]
                ]),
                'subtotal' => 50000,
                'shipping_cost' => 10000,
                'discount' => 0,
                'total' => 60000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_service' => 'jne',
                'customer_notes' => 'Test order untuk auto-cancel',
                'admin_notes' => '',
                'confirmation_deadline' => Carbon::now()->subHours(25), // Sudah 25 jam
            ]);

            $this->info("✓ Order dibuat: #{$testOrder->order_number}");
            $this->line("  Status: {$testOrder->status}");
            $this->line("  Deadline: {$testOrder->confirmation_deadline->format('Y-m-d H:i:s')}");
            $this->line("  Sekarang: " . Carbon::now()->format('Y-m-d H:i:s'));
            $this->line('');

            // TEST 2: Jalankan auto-cancel command
            $this->info('TEST 2: Jalankan orders:auto-cancel-unconfirmed command');
            $this->line(str_repeat('-', 60));
            $this->call('orders:auto-cancel-unconfirmed');
            $this->line('');

            // TEST 3: Verifikasi order berhasil di-cancel
            $this->info('TEST 3: Verifikasi order berhasil di-cancel');
            $this->line(str_repeat('-', 60));

            $testOrder->refresh();

            if ($testOrder->status === 'cancelled') {
                $this->info("✓ Status berhasil berubah menjadi: {$testOrder->status}");
                $cancelledAt = $testOrder->cancelled_at ? $testOrder->cancelled_at->format('Y-m-d H:i:s') : 'N/A';
                $this->info("✓ Cancelled at: {$cancelledAt}");
            } else {
                $this->error("GAGAL: Status masih {$testOrder->status}, seharusnya 'cancelled'");
                return Command::FAILURE;
            }

            $this->line('');

            // TEST 4: Verifikasi order yang belum deadline tidak di-cancel
            $this->info('TEST 4: Verifikasi order yang belum deadline tidak di-cancel');
            $this->line(str_repeat('-', 60));

            $futureOrder = Order::create([
                'user_id' => $user->id,
                'customer_address_id' => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'items' => json_encode([
                    [
                        'product_id' => 1,
                        'name' => 'Test Product 2',
                        'quantity' => 1,
                        'price' => 50000,
                    ]
                ]),
                'subtotal' => 50000,
                'shipping_cost' => 10000,
                'discount' => 0,
                'total' => 60000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_service' => 'jne',
                'customer_notes' => 'Test order untuk verifikasi timeout',
                'admin_notes' => '',
                'confirmation_deadline' => Carbon::now()->addHours(5), // Masih 5 jam lagi
            ]);

            $this->info("✓ Order dibuat: #{$futureOrder->order_number}");
            $this->line("  Status: {$futureOrder->status}");
            $this->line("  Deadline: {$futureOrder->confirmation_deadline->format('Y-m-d H:i:s')}");
            $this->line("  Waktu tersisa: " . $futureOrder->confirmation_deadline->diffInHours(Carbon::now()) . " jam");
            $this->line('');

            // Jalankan command lagi
            $this->call('orders:auto-cancel-unconfirmed');
            $this->line('');

            $futureOrder->refresh();
            if ($futureOrder->status === 'pending') {
                $this->info('✓ Order dengan deadline belum tercapai TIDAK di-cancel');
                $this->info("✓ Status masih: {$futureOrder->status}");
            } else {
                $this->error('GAGAL: Order di-cancel padahal deadline belum tercapai');
                return Command::FAILURE;
            }

            $this->line('');
            $this->info(str_repeat('=', 60));
            $this->info('✅ SEMUA TEST BERHASIL!');
            $this->info(str_repeat('=', 60));

            // Cleanup
            $this->line('');
            $this->info('Cleanup: Menghapus test orders...');
            Order::where('id', $testOrder->id)->delete();
            Order::where('id', $futureOrder->id)->delete();
            $this->info('✓ Cleanup selesai');
            $this->line('');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
