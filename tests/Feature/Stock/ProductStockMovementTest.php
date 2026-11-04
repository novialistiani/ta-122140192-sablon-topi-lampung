<?php

namespace Tests\Feature\Stock;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'name' => 'Kaos Uji Stok',
            'category' => 'kaos',
            'price' => 50000,
            'stock' => 20,
        ], $extra);
    }

    private function variant(Product $product, string $color, string $size, int $stock): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'color' => $color,
            'size' => $size,
            'price' => 50000,
            'stock' => $stock,
        ]);
    }

    public function test_creating_product_without_variants_records_initial_stock(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('api.products.store'), $this->payload());

        $response->assertStatus(201);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $response->json('data.id'),
            'product_variant_id' => null,
            'type' => 'in',
            'quantity_change' => 20,
            'stock_before' => 0,
            'stock_after' => 20,
            'reason' => 'initial_stock',
        ]);
    }

    public function test_creating_product_with_variants_records_initial_stock_per_variant_only(): void
    {
        $variants = json_encode([
            ['color' => 'Hitam', 'size' => 'L', 'price' => 50000, 'stock' => 7],
            ['color' => 'Putih', 'size' => 'M', 'price' => 50000, 'stock' => 3],
        ]);

        $this->actingAs($this->admin, 'admin')
            ->postJson(route('api.products.store'), $this->payload(['variants' => $variants]))
            ->assertStatus(201);

        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('stock_movements', ['quantity_change' => 7, 'variant_label' => 'Hitam / L']);
        $this->assertDatabaseHas('stock_movements', ['quantity_change' => 3, 'variant_label' => 'Putih / M']);
        $this->assertDatabaseMissing('stock_movements', ['product_variant_id' => null]);
    }

    public function test_changing_product_stock_records_manual_adjustment(): void
    {
        $product = Product::factory()->create(['stock' => 20, 'category' => 'kaos']);

        $this->actingAs($this->admin, 'admin')
            ->putJson(route('api.products.update', $product->id), $this->payload(['name' => $product->name, 'stock' => 25]))
            ->assertOk();

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity_change' => 5,
            'stock_before' => 20,
            'stock_after' => 25,
            'reason' => 'manual_adjustment',
        ]);
    }

    public function test_saving_product_without_stock_change_records_nothing(): void
    {
        $product = Product::factory()->create(['stock' => 20, 'category' => 'kaos']);

        $this->actingAs($this->admin, 'admin')
            ->putJson(route('api.products.update', $product->id), $this->payload(['name' => $product->name, 'stock' => 20]))
            ->assertOk();

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_changing_variant_stock_records_adjustment_on_variant_level(): void
    {
        $product = Product::factory()->create(['stock' => 20, 'category' => 'kaos']);
        $variant = $this->variant($product, 'Hitam', 'L', 10);

        $variants = json_encode([['color' => 'Hitam', 'size' => 'L', 'price' => 50000, 'stock' => 15]]);

        $this->actingAs($this->admin, 'admin')
            ->putJson(route('api.products.update', $product->id), $this->payload(['name' => $product->name, 'variants' => $variants]))
            ->assertOk();

        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'quantity_change' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
            'reason' => 'manual_adjustment',
        ]);
        $this->assertDatabaseMissing('stock_movements', ['product_variant_id' => null]);
    }

    public function test_adding_new_variant_on_update_records_initial_stock(): void
    {
        $product = Product::factory()->create(['stock' => 20, 'category' => 'kaos']);
        $this->variant($product, 'Hitam', 'L', 10);

        $variants = json_encode([
            ['color' => 'Hitam', 'size' => 'L', 'price' => 50000, 'stock' => 10],
            ['color' => 'Putih', 'size' => 'M', 'price' => 50000, 'stock' => 4],
        ]);

        $this->actingAs($this->admin, 'admin')
            ->putJson(route('api.products.update', $product->id), $this->payload(['name' => $product->name, 'variants' => $variants]))
            ->assertOk();

        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('stock_movements', [
            'variant_label' => 'Putih / M',
            'quantity_change' => 4,
            'reason' => 'initial_stock',
        ]);
    }

    public function test_removing_variant_records_stock_out_adjustment(): void
    {
        $product = Product::factory()->create(['stock' => 20, 'category' => 'kaos']);
        $this->variant($product, 'Hitam', 'L', 10);
        $removed = $this->variant($product, 'Putih', 'M', 6);

        $variants = json_encode([['color' => 'Hitam', 'size' => 'L', 'price' => 50000, 'stock' => 10]]);

        $this->actingAs($this->admin, 'admin')
            ->putJson(route('api.products.update', $product->id), $this->payload(['name' => $product->name, 'variants' => $variants]))
            ->assertOk();

        $this->assertDatabaseMissing('product_variants', ['id' => $removed->id]);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $removed->id,
            'quantity_change' => -6,
            'stock_before' => 6,
            'stock_after' => 0,
            'reason' => 'manual_adjustment',
        ]);
    }
}