<?php

namespace Tests\Feature\Stock;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosStockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->product = Product::factory()->create(['stock' => 20, 'price' => 50000]);
        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'color' => 'Putih',
            'size' => 'M',
            'price' => 50000,
            'stock' => 10,
        ]);
    }

    private function sell(array $items, string $method = 'cash', ?int $cash = 500000)
    {
            return $this->actingAs($this->admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'items' => $items,
            'payment_method' => $method,
            'cash_received' => $cash,
        ]);
    }

    public function test_pos_sale_with_variant_records_stock_out_movement(): void
    {
        $response = $this->sell([
            ['product_id' => $this->product->id, 'variant_id' => $this->variant->id, 'quantity' => 2],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $this->variant->id,
            'type' => 'out',
            'quantity_change' => -2,
            'stock_before' => 10,
            'stock_after' => 8,
            'reason' => 'pos_sale',
            'reference_type' => 'order',
            'reference_id' => $response->json('order_id'),
        ]);
        $this->assertEquals(8, $this->variant->fresh()->stock);
    }

    public function test_pos_sale_without_variant_records_product_level_movement(): void
    {
        $this->sell([
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 5],
        ])->assertOk();

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'product_variant_id' => null,
            'quantity_change' => -5,
            'stock_before' => 20,
            'stock_after' => 15,
            'reason' => 'pos_sale',
        ]);
    }

    public function test_pos_sale_records_the_cashier_who_processed_it(): void
    {
        $this->sell([
            ['product_id' => $this->product->id, 'variant_id' => $this->variant->id, 'quantity' => 1],
        ])->assertOk();

        $this->assertEquals(
            $this->admin->id,
            StockMovement::where('reason', 'pos_sale')->value('admin_id')
        );
    }

    public function test_failed_pos_sale_with_insufficient_stock_records_no_movement(): void
    {
        $this->sell([
            ['product_id' => $this->product->id, 'variant_id' => $this->variant->id, 'quantity' => 99],
        ])->assertStatus(400);

        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertEquals(10, $this->variant->fresh()->stock);
    }

    public function test_pos_sale_with_several_items_records_one_movement_per_item(): void
    {
        $this->sell([
            ['product_id' => $this->product->id, 'variant_id' => $this->variant->id, 'quantity' => 1],
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 2],
        ])->assertOk();

        $this->assertDatabaseCount('stock_movements', 2);
    }
}