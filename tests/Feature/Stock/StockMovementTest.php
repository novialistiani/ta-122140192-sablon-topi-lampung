<?php

namespace Tests\Feature\Stock;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['stock' => 20]);
        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'color' => 'Hitam',
            'size' => 'L',
            'price' => 50000,
            'stock' => 10,
        ]);
    }

    private function orderFor(int $quantity = 3, bool $withVariant = true): Order
    {
        return Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 50000 * $quantity,
            'total' => 50000 * $quantity,
            'items' => [[
                'product_id' => $this->product->id,
                'variant_id' => $withVariant ? $this->variant->id : null,
                'name' => $this->product->name,
                'price' => 50000,
                'quantity' => $quantity,
                'color' => $withVariant ? 'Hitam' : null,
                'size' => $withVariant ? 'L' : null,
            ]],
        ]);
    }

    private function approve(Order $order)
    {
        return $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=regular');
    }

    public function test_approving_order_records_stock_out_movement_for_variant(): void
    {
        $order = $this->orderFor(3);

        $this->approve($order);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'out',
            'quantity_change' => -3,
            'stock_before' => 10,
            'stock_after' => 7,
            'reason' => 'order_approved',
            'reference_type' => 'order',
            'reference_id' => $order->id,
        ]);
        $this->assertEquals(7, $this->variant->fresh()->stock);
    }

    public function test_movement_stores_product_name_and_variant_label_snapshot(): void
    {
        $this->approve($this->orderFor(1));

        $this->assertDatabaseHas('stock_movements', [
            'product_name' => $this->product->name,
            'variant_label' => 'Hitam / L',
        ]);
    }

    public function test_approving_order_without_variant_records_product_level_movement(): void
    {
        $order = $this->orderFor(4, false);

        $this->approve($order);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'product_variant_id' => null,
            'quantity_change' => -4,
            'stock_before' => 20,
            'stock_after' => 16,
        ]);
    }

    public function test_approval_with_insufficient_stock_records_no_movement(): void
    {
        $order = $this->orderFor(50);

        $this->approve($order);

        $this->assertEquals('pending', $order->fresh()->status);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_rejecting_approved_order_records_stock_in_movement(): void
    {
        $order = $this->orderFor(3);
        $this->approve($order);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', [
                'reason' => 'Desain tidak sesuai',
            ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'quantity_change' => 3,
            'stock_before' => 7,
            'stock_after' => 10,
            'reason' => 'order_restored',
        ]);
        $this->assertEquals(10, $this->variant->fresh()->stock);
    }

    public function test_auto_cancelled_unpaid_order_restores_stock_and_records_movement(): void
    {
        $order = $this->orderFor(2);
        $this->approve($order);

        $order->fresh()->forceFill(['payment_deadline' => now()->subMinutes(5)])->save();
        Artisan::call('orders:cancel-expired');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $this->variant->id,
            'quantity_change' => 2,
            'reason' => 'order_restored',
            'reference_id' => $order->id,
        ]);
        $this->assertEquals(10, $this->variant->fresh()->stock);
    }

    public function test_movement_balance_is_consistent_with_current_stock(): void
    {
        $order = $this->orderFor(3);
        $this->approve($order);
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', ['reason' => 'Batal']);

        $net = \App\Models\StockMovement::where('product_variant_id', $this->variant->id)->sum('quantity_change');

        $this->assertEquals(0, $net);
        $this->assertEquals(10, $this->variant->fresh()->stock);
    }
    
}