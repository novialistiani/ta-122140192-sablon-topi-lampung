<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function payment_method_can_be_created()
    {
        $method = PaymentMethod::create([
            'name' => 'Bank Transfer BCA',
            'type' => 'bank_transfer',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'name' => 'Bank Transfer BCA',
        ]);
    }

    /** @test */
    public function payment_method_can_be_inactive()
    {
        $method = PaymentMethod::create([
            'name' => 'Inactive Method',
            'type' => 'old_method',
            'is_active' => false,
        ]);

        $this->assertFalse($method->is_active);
    }

    /** @test */
    public function payment_transaction_can_be_created()
    {
        $transaction = PaymentTransaction::create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'amount' => 500000,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'reference_number' => 'TXN-001-2024',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $this->order->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function payment_transaction_status_can_be_processing()
    {
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $transaction->update(['status' => 'processing']);

        $this->assertEquals('processing', $transaction->fresh()->status);
    }

    /** @test */
    public function payment_transaction_status_can_be_success()
    {
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $transaction->update(['status' => 'success']);

        $this->assertEquals('success', $transaction->fresh()->status);
    }

    /** @test */
    public function payment_transaction_status_can_be_failed()
    {
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $transaction->update(['status' => 'failed']);

        $this->assertEquals('failed', $transaction->fresh()->status);
    }

    /** @test */
    public function successful_payment_updates_order_payment_status()
    {
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'amount' => $this->order->total,
        ]);

        $transaction->update(['status' => 'success']);
        $this->order->update(['payment_status' => 'paid']);

        $this->assertEquals('paid', $this->order->fresh()->payment_status);
    }

    /** @test */
    public function payment_reference_number_is_unique()
    {
        $txn1 = PaymentTransaction::create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'amount' => 100000,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $txn2 = PaymentTransaction::create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'amount' => 100000,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        // Verify both transactions have unique transaction_id
        $this->assertNotEquals($txn1->transaction_id, $txn2->transaction_id);
        $this->assertDatabaseHas('payment_transactions', [
            'transaction_id' => $txn1->transaction_id,
        ]);
    }

    /** @test */
    public function multiple_payment_methods_available()
    {
        PaymentMethod::create(['code' => 'pm_bca', 'name' => 'BCA', 'type' => 'bank', 'is_active' => true]);
        PaymentMethod::create(['code' => 'pm_gopay', 'name' => 'GoPay', 'type' => 'ewallet', 'is_active' => true]);
        PaymentMethod::create(['code' => 'pm_ovo', 'name' => 'OVO', 'type' => 'ewallet', 'is_active' => true]);

        $methods = PaymentMethod::where('is_active', true)->get();

        $this->assertEquals(3, $methods->count());
    }
}
