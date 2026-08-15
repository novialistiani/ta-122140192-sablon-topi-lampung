<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function product_can_be_created()
    {
        $product = Product::factory()->create();

        $this->assertDatabaseHas('products', [
            'name' => $product->name,
            'slug' => $product->slug,
        ]);
    }

    /** @test */
    public function product_can_be_read()
    {
        $product = Product::factory()->create();

        $retrieved = Product::find($product->id);

        $this->assertEquals($product->name, $retrieved->name);
        $this->assertEquals($product->price, $retrieved->price);
    }

    /** @test */
    public function product_data_can_be_updated()
    {
        $product = Product::factory()->create();
        $newPrice = 150000.00;

        $product->update(['price' => $newPrice]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => $newPrice,
        ]);
    }

    /** @test */
    public function product_can_be_deleted()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertDatabaseMissing('products', [
            'id' => $productId,
        ]);
    }

    /** @test */
    public function product_stock_can_be_incremented()
    {
        $product = Product::factory()->create(['stock' => 10]);
        $originalStock = $product->stock;

        $product->increment('stock', 5);

        $this->assertEquals($originalStock + 5, $product->refresh()->stock);
    }

    /** @test */
    public function product_stock_can_be_decremented()
    {
        $product = Product::factory()->create(['stock' => 20]);
        $originalStock = $product->stock;

        $product->decrement('stock', 5);

        $this->assertEquals($originalStock - 5, $product->refresh()->stock);
    }

    /** @test */
    public function product_can_be_activated()
    {
        $product = Product::factory()->create(['is_active' => false]);

        $product->update(['is_active' => true]);

        $this->assertTrue($product->refresh()->is_active);
    }

    /** @test */
    public function product_can_be_deactivated()
    {
        $product = Product::factory()->create(['is_active' => true]);

        $product->update(['is_active' => false]);

        $this->assertFalse($product->refresh()->is_active);
    }

    /** @test */
    public function multiple_products_can_be_listed()
    {
        $products = Product::factory()->count(5)->create();

        $retrieved = Product::all();

        $this->assertGreaterThanOrEqual(5, $retrieved->count());
    }
}
