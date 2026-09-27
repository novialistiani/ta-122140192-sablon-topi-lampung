<?php

namespace Tests\Feature\Cart;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_keranjang_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('keranjang'));

        $response->assertOk();
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 50000,
        ]);

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('keranjang'));
        $this->assertNotEmpty(session('cart'));
    }

    public function test_adding_product_with_zero_stock_fails(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 0]);

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }

    public function test_cart_quantity_cannot_exceed_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3, 'price' => 20000]);

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $cart = session('cart');
        $item = collect($cart)->first();

        $this->assertEquals(3, $item['quantity']);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 30000]);

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $key = array_key_first(session('cart'));

        $response = $this->actingAs($user)->patch(route('cart.update', $key), [
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('keranjang'));
        $this->assertEquals(5, session('cart')[$key]['quantity']);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 30000]);

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $key = array_key_first(session('cart'));

        $response = $this->actingAs($user)->delete(route('cart.remove', $key));

        $response->assertRedirect(route('keranjang'));
        $this->assertEmpty(session('cart', []));
    }

    public function test_user_can_remove_selected_items_from_cart(): void
    {
        $user = User::factory()->create();
        $productA = Product::factory()->create(['stock' => 10, 'price' => 25000]);
        $productB = Product::factory()->create(['stock' => 10, 'price' => 40000]);

        $this->actingAs($user)->post(route('cart.add'), ['product_id' => $productA->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('cart.add'), ['product_id' => $productB->id, 'quantity' => 1]);

        $keys = array_keys(session('cart'));

        $response = $this->actingAs($user)->delete(route('cart.bulk-remove'), [
            'keys' => $keys,
        ]);

        $response->assertRedirect(route('keranjang'));
        $this->assertEmpty(session('cart', []));
    }

    public function test_checkout_creates_order_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 60000]);

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('checkout'));

        $response->assertRedirect(route('order-list'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $this->assertEmpty(session('cart', []));
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('checkout'));

        $response->assertRedirect(route('keranjang'));
        $response->assertSessionHas('error');
    }
}
