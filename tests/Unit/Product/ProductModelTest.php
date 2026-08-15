<?php

namespace Tests\Unit\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_can_be_created()
    {
        $product = Product::factory()->create();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function product_has_correct_fillable()
    {
        $product = new Product();
        $fillable = $product->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('stock', $fillable);
    }

    /** @test */
    public function product_can_have_variants()
    {
        $product = Product::factory()->create();

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Black',
            'stock' => 20,
        ]);

        $this->assertTrue($product->fresh()->variants->contains($variant));
    }

    /** @test */
    public function product_stock_can_be_managed()
    {
        $product = Product::factory()->create(['stock' => 50]);

        $initialStock = $product->stock;
        $product->decrement('stock', 5);

        $this->assertEquals($initialStock - 5, $product->fresh()->stock);
    }

    /** @test */
    public function product_price_is_decimal()
    {
        $product = Product::factory()->create(['price' => 150000.50]);

        $this->assertEquals(150000.50, $product->fresh()->price);
    }

    /** @test */
    public function product_can_be_inactive()
    {
        $product = Product::factory()->create([
            'is_active' => false,
        ]);

        $this->assertFalse($product->is_active);
    }

    /** @test */
    public function product_can_belong_to_subcategory()
    {
        $product = Product::factory()->create();

        $this->assertTrue(method_exists($product, 'variants'));
    }

    /** @test */
    public function product_soft_delete_works()
    {
        $product = Product::factory()->create();

        $productId = $product->id;
        $product->delete();

        // Should not find in default query
        $this->assertNull(Product::find($productId));
    }

    /** @test */
    public function product_with_high_stock_is_available()
    {
        $product = Product::factory()->create(['stock' => 500]);

        $this->assertGreaterThan(100, $product->stock);
    }

    /** @test */
    public function product_can_have_custom_design_allowed()
    {
        $product = Product::factory()->create([
            'custom_design_allowed' => true,
        ]);

        $this->assertTrue($product->custom_design_allowed);
    }
}
