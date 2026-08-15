<?php

namespace Tests\Unit\Payment;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
            'type' => 'bank_transfer',
        ]);
    }

    /** @test */
    public function payment_method_can_be_inactive()
    {
        $method = PaymentMethod::create([
            'name' => 'Old Payment Method',
            'type' => 'old_type',
            'is_active' => false,
        ]);

        $this->assertFalse($method->is_active);
    }

    /** @test */
    public function payment_transaction_can_be_created()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $transaction = PaymentTransaction::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'payment_method' => 'va',
            'payment_channel' => 'bca',
            'amount' => 500000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function payment_transaction_status_can_be_updated()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user->id,
        ]);

        $transaction->update(['status' => 'paid']);
        $this->assertEquals('paid', $transaction->fresh()->status);
    }

    /** @test */
    public function payment_transaction_belongs_to_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($transaction->order->is($order));
    }

    /** @test */
    public function payment_transaction_belongs_to_user()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($transaction->user->is($this->user));
    }

    /** @test */
    public function payment_transaction_can_track_amount()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $transaction = PaymentTransaction::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'payment_method' => 'va',
            'amount' => 1500000.50,
            'status' => 'pending',
        ]);

        $this->assertEquals(1500000.50, $transaction->fresh()->amount);
    }

    /** @test */
    public function payment_method_has_multiple_types()
    {
        $types = ['bank_transfer', 'e_wallet', 'credit_card', 'virtual_account'];

        foreach ($types as $type) {
            PaymentMethod::create([
                'code' => 'pm_' . $type,
                'name' => 'Payment ' . $type,
                'type' => $type,
                'is_active' => true,
            ]);
        }

        $this->assertEquals(4, PaymentMethod::count());
    }

    /** @test */
    public function payment_transaction_status_workflow()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $transaction = PaymentTransaction::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $statuses = ['pending', 'paid', 'expired'];

        foreach ($statuses as $status) {
            $transaction->update(['status' => $status]);
            $this->assertEquals($status, $transaction->fresh()->status);
        }
    }
}
