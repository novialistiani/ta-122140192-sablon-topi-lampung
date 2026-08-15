<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function order_create_with_valid_data()
    {
        $data = [
            'user_id' => $this->user->id,
            'order_number' => 'ORD-001-2025',
            'items' => json_encode([
                ['product_id' => 1, 'quantity' => 2, 'price' => 50000]
            ]),
            'subtotal' => 100000,
            'total' => 100000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ];

        $order = Order::create($data);

        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-001-2025',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function order_read_retrieves_correct_data()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $retrieved = Order::find($order->id);

        $this->assertEquals($order->id, $retrieved->id);
        $this->assertEquals($this->user->id, $retrieved->user_id);
    }

    /** @test */
    public function order_update_status_to_processing()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'processing']);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function order_update_status_to_completed()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
        ]);

        $order->update(['status' => 'completed']);

        $this->assertEquals('completed', $order->fresh()->status);
    }

    /** @test */
    public function order_can_be_cancelled()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'cancelled']);

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function order_payment_status_can_be_marked_paid()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'unpaid',
        ]);

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    /** @test */
    public function order_delete_removes_from_database()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $orderId = $order->id;

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $orderId]);
    }

    /** @test */
    public function order_can_have_admin_notes()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $notes = 'Order perlu verifikasi tambahan';

        $order->update(['admin_notes' => $notes]);

        $this->assertEquals($notes, $order->fresh()->admin_notes);
    }

    /** @test */
    public function order_items_json_casting_works()
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

        $retrieved = Order::find($order->id);
        $this->assertIsArray($retrieved->items);
        $this->assertCount(2, $retrieved->items);
    }

    /** @test */
    public function order_list_returns_all_user_orders()
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $orders = Order::where('user_id', $this->user->id)->get();

        $this->assertEquals(3, $orders->count());
    }
}
