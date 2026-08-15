<?php

namespace Tests\Unit\CustomDesign;

use App\Models\CustomDesignOrder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDesignOrderModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['custom_design_allowed' => true]);
    }

    /** @test */
    public function custom_design_order_can_be_created()
    {
        $order = CustomDesignOrder::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'additional_description' => 'Design custom topi dengan logo',
            'quantity' => 10,
            'status' => 'pending',
            'total_price' => 500000,
        ]);

        $this->assertDatabaseHas('custom_design_orders', [
            'additional_description' => 'Design custom topi dengan logo',
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function custom_design_order_belongs_to_user()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertTrue($order->user->is($this->user));
    }

    /** @test */
    public function custom_design_order_belongs_to_product()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertTrue($order->product->is($this->product));
    }

    /** @test */
    public function custom_design_order_can_have_uploads()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->assertTrue(method_exists($order, 'uploads'));
    }

    /** @test */
    public function custom_design_order_status_can_be_changed()
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
    public function custom_design_order_can_track_quantity()
    {
        $order = CustomDesignOrder::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $this->assertEquals(5, $order->quantity);
    }

    /** @test */
    public function custom_design_order_has_correct_price()
    {
        $order = CustomDesignOrder::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'total_price' => 1000000,
            'status' => 'pending',
        ]);

        $this->assertEquals(1000000, $order->fresh()->total_price);
    }

    /** @test */
    public function custom_design_order_can_be_approved_by_admin()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $order->update([
            'status' => 'approved',
            'admin_notes' => 'Design sudah disetujui',
        ]);

        $this->assertEquals('approved', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->admin_notes);
    }

    /** @test */
    public function custom_design_order_can_be_rejected()
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'pending',
        ]);

        $order->update([
            'status' => 'rejected',
            'admin_notes' => 'Design tidak sesuai standar',
        ]);

        $this->assertEquals('rejected', $order->fresh()->status);
    }

    /** @test */
    public function custom_design_order_payment_status_can_be_tracked()
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
}
