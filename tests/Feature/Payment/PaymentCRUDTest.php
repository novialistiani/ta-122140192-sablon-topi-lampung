<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCRUDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function payment_method_create()
    {
        $data = [
            'name' => 'Bank Transfer BCA',
            'code' => 'BCA-TRANSFER',
            'type' => 'bank_transfer',
            'is_active' => true,
        ];

        $method = PaymentMethod::create($data);

        $this->assertDatabaseHas('payment_methods', [
            'name' => 'Bank Transfer BCA',
            'code' => 'BCA-TRANSFER',
        ]);
    }

    /** @test */
    public function payment_method_read()
    {
        $method = PaymentMethod::factory()->create();
        $retrieved = PaymentMethod::find($method->id);

        $this->assertEquals($method->id, $retrieved->id);
        $this->assertEquals($method->name, $retrieved->name);
    }

    /** @test */
    public function payment_method_update()
    {
        $method = PaymentMethod::factory()->create([
            'name' => 'Old Name',
            'is_active' => true,
        ]);

        $method->update([
            'name' => 'Updated Name',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $method->id,
            'name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function payment_method_delete()
    {
        $method = PaymentMethod::factory()->create();
        $methodId = $method->id;

        $method->delete();

        $this->assertDatabaseMissing('payment_methods', ['id' => $methodId]);
    }

    /** @test */
    public function payment_method_can_be_activated()
    {
        $method = PaymentMethod::factory()->create(['is_active' => false]);

        $method->update(['is_active' => true]);

        $this->assertTrue($method->fresh()->is_active);
    }

    /** @test */
    public function payment_transaction_create()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $data = [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'payment_method' => 'bank_transfer',
            'amount' => 100000,
            'status' => 'pending',
        ];

        $transaction = PaymentTransaction::create($data);

        $this->assertDatabaseHas('payment_transactions', [
            'payment_method' => 'bank_transfer',
            'amount' => 100000,
        ]);
    }

    /** @test */
    public function payment_transaction_update_status()
    {
        $transaction = PaymentTransaction::factory()->create([
            'status' => 'pending',
        ]);

        $transaction->update(['status' => 'success']);

        $this->assertEquals('success', $transaction->fresh()->status);
    }

    /** @test */
    public function payment_transaction_delete()
    {
        $transaction = PaymentTransaction::factory()->create();
        $transactionId = $transaction->id;

        $transaction->delete();

        $this->assertDatabaseMissing('payment_transactions', ['id' => $transactionId]);
    }

    /** @test */
    public function payment_transaction_status_workflow()
    {
        $transaction = PaymentTransaction::factory()->create(['status' => 'pending']);

        $transaction->update(['status' => 'processing']);
        $this->assertEquals('processing', $transaction->fresh()->status);

        $transaction->update(['status' => 'success']);
        $this->assertEquals('success', $transaction->fresh()->status);
    }

    /** @test */
    public function payment_methods_list()
    {
        PaymentMethod::factory()->count(3)->create();

        $methods = PaymentMethod::all();

        $this->assertGreaterThanOrEqual(3, $methods->count());
    }
}
