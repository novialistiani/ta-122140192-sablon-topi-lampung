<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['price' => 100000, 'stock' => 50]);
    }

    /** @test */
    public function user_can_create_order()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-001',
            'items' => json_encode([
                ['product_id' => $this->product->id, 'quantity' => 2, 'price' => 100000]
            ]),
            'subtotal' => 200000,
            'total' => 200000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-001',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function order_status_can_change_from_pending_to_processing()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $order->update(['status' => 'processing']);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function order_status_can_change_to_completed()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'processing']);

        $order->update(['status' => 'completed']);

        $this->assertEquals('completed', $order->fresh()->status);
    }

    /** @test */
    public function order_can_be_cancelled()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $order->update(['status' => 'cancelled']);

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function order_payment_status_can_be_marked_paid()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'unpaid'
        ]);

        $order->update(['payment_status' => 'paid', 'paid_at' => now()]);

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    /** @test */
    public function order_items_json_cast_works()
    {
        $items = [
            ['product_id' => 1, 'quantity' => 2, 'price' => 50000],
            ['product_id' => 2, 'quantity' => 1, 'price' => 75000],
        ];

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-JSON-001',
            'items' => $items,
            'subtotal' => 175000,
            'total' => 175000,
            'status' => 'pending',
        ]);

        $this->assertIsArray($order->fresh()->items);
        $this->assertEquals(2, count($order->fresh()->items));
    }

    /** @test */
    public function order_total_calculation_correct()
    {
        $subtotal = 200000;
        $shipping = 20000;
        $discount = 10000;
        $total = $subtotal + $shipping - $discount;

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-CALC-001',
            'items' => json_encode([]),
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'status' => 'pending',
        ]);

        $this->assertEquals($total, $order->fresh()->total);
    }

    /** @test */
    public function order_formatted_last_action_timestamp_shows_correct_date()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $formatted = $order->formatted_last_action;
        $this->assertIsString($formatted);
        $this->assertNotEquals('N/A', $formatted);
    }
}
