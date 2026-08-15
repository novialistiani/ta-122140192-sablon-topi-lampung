<?php

namespace Tests\Unit\Order;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function order_can_be_created()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-001',
            'items' => json_encode([
                ['product_id' => 1, 'quantity' => 2, 'price' => 50000]
            ]),
            'subtotal' => 100000,
            'shipping_cost' => 10000,
            'total' => 110000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-001',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function order_items_are_json_cast()
    {
        $items = [
            ['product_id' => 1, 'quantity' => 2, 'price' => 50000],
            ['product_id' => 2, 'quantity' => 1, 'price' => 75000],
        ];

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-002',
            'items' => $items,
            'subtotal' => 175000,
            'total' => 175000,
            'status' => 'pending',
        ]);

        $freshOrder = $order->fresh();
        $this->assertIsArray($freshOrder->items);
        $this->assertEquals(2, count($freshOrder->items));
    }

    /** @test */
    public function order_status_can_be_updated()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $order->update(['status' => 'processing']);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function order_has_correct_timestamps()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
    }

    /** @test */
    public function order_can_belong_to_user()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->assertTrue($order->user->is($this->user));
    }

    /** @test */
    public function order_total_is_decimal()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-003',
            'items' => json_encode([]),
            'subtotal' => 100000.50,
            'total' => 100000.50,
            'status' => 'pending',
        ]);

        $this->assertEquals(100000.50, $order->fresh()->total);
    }

    /** @test */
    public function order_can_have_multiple_statuses()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        
        foreach ($statuses as $status) {
            $order->update(['status' => $status]);
            $this->assertEquals($status, $order->fresh()->status);
        }
    }

    /** @test */
    public function order_last_action_timestamp_works()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
            'processing_at' => now(),
        ]);

        // Test the attribute works
        $this->assertNotNull($order->last_action_timestamp);
    }

    /** @test */
    public function order_formatted_last_action_works()
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

    /** @test */
    public function order_can_track_payment_status()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'unpaid',
        ]);

        $this->assertEquals('unpaid', $order->payment_status);
        
        $order->update(['payment_status' => 'paid']);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }
}
