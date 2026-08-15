<?php

namespace Tests\Feature\CustomDesign;

use App\Models\CustomDesignOrder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDesignOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['custom_design_allowed' => true]);
    }

    /** @test */
    public function user_can_create_custom_design_order()
    {
        $order = CustomDesignOrder::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'design_description' => 'Logo berwarna merah di depan',
            'quantity' => 10,
            'status' => 'pending',
            'total_price' => 500000,
        ]);

        $this->assertDatabaseHas('custom_design_orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function custom_design_order_status_can_change_to_approved()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'approved']);

        $this->assertEquals('approved', $order->fresh()->status);
    }

    /** @test */
    public function custom_design_order_status_can_change_to_rejected()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'rejected']);

        $this->assertEquals('rejected', $order->fresh()->status);
    }

    /** @test */
    public function custom_design_order_can_have_admin_notes()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $notes = 'Design harus lebih besar di area depan';
        $order->update(['admin_notes' => $notes]);

        $this->assertEquals($notes, $order->fresh()->admin_notes);
    }

    /** @test */
    public function custom_design_payment_status_workflow()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'payment_status' => 'unpaid',
        ]);

        $this->assertEquals('unpaid', $order->payment_status);

        $order->update(['payment_status' => 'paid']);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /** @test */
    public function custom_design_order_quantity_is_tracked()
    {
        $order = CustomDesignOrder::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
            'status' => 'pending',
        ]);

        $this->assertEquals(25, $order->fresh()->quantity);
    }

    /** @test */
    public function custom_design_order_price_calculated()
    {
        $basePrice = 100000;
        $quantity = 5;
        $totalPrice = $basePrice * $quantity;

        $order = CustomDesignOrder::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        $this->assertEquals($totalPrice, $order->fresh()->total_price);
    }
}
