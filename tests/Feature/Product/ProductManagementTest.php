<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_product()
    {
        $data = [
            'name' => 'Topi Baseball',
            'description' => 'Topi baseball berkualitas tinggi',
            'price' => 100000,
            'stock' => 50,
            'category' => 'headwear',
            'is_active' => true,
        ];

        $product = Product::create($data);

        $this->assertDatabaseHas('products', [
            'name' => 'Topi Baseball',
            'price' => 100000,
        ]);
    }

    /** @test */
    public function admin_can_update_product()
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 100000,
        ]);

        $product->update([
            'name' => 'Updated Name',
            'price' => 150000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 150000,
        ]);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    /** @test */
    public function product_can_have_variants()
    {
        $product = Product::factory()->create();

        $variant = $product->variants()->create([
            'name' => 'Merah',
            'size' => 'M',
            'stock' => 20,
        ]);

        $this->assertTrue($product->variants->contains($variant));
    }

    /** @test */
    public function product_stock_can_be_decreased()
    {
        $product = Product::factory()->create(['stock' => 100]);

        $product->decrement('stock', 10);

        $this->assertEquals(90, $product->fresh()->stock);
    }

    /** @test */
    public function product_stock_can_be_increased()
    {
        $product = Product::factory()->create(['stock' => 50]);

        $product->increment('stock', 10);

        $this->assertEquals(60, $product->fresh()->stock);
    }

    /** @test */
    public function inactive_product_not_shown_in_list()
    {
        Product::factory()->create(['is_active' => false]);
        Product::factory()->create(['is_active' => true]);

        $activeProducts = Product::where('is_active', true)->get();

        $this->assertEquals(1, $activeProducts->count());
    }

    /** @test */
    public function product_can_be_searched_by_name()
    {
        Product::factory()->create(['name' => 'Topi Klasik']);
        Product::factory()->create(['name' => 'Sepatu Olahraga']);

        $results = Product::where('name', 'like', '%Topi%')->get();

        $this->assertEquals(1, $results->count());
    }
}
