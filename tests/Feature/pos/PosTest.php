<?php

namespace Tests\Feature\Pos;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_can_be_rendered_by_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.pos'));

        $response->assertOk();
    }

    public function test_admin_can_search_active_products(): void
    {
        $admin = Admin::factory()->create();
        Product::factory()->create(['name' => 'Kaos Polos', 'is_active' => true]);
        Product::factory()->create(['name' => 'Topi Custom', 'is_active' => true]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.pos.products', ['search' => 'Kaos']));

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_pos_transaction_can_be_created_with_cash_payment(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 50000]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'cash_received' => 150000,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'source' => 'pos',
            'status' => 'completed',
            'payment_status' => 'paid',
            'pos_payment_method' => 'cash',
        ]);
    }

    public function test_pos_transaction_reduces_product_stock(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 30000]);

        $this->actingAs($admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'payment_method' => 'qris',
        ]);

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_pos_transaction_fails_when_stock_insufficient(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create(['stock' => 1, 'price' => 30000]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertEquals(1, $product->fresh()->stock);
    }

    public function test_pos_checkout_calculates_correct_change(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 25000]);

        $response = $this->actingAs($admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'cash_received' => 60000,
        ]);

        $response->assertJson([
            'success' => true,
            'total' => 50000,
            'change' => 10000,
        ]);
    }

    public function test_pos_cashier_id_recorded_as_authenticated_admin(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 40000]);

        $this->actingAs($admin, 'admin')->postJson(route('admin.pos.checkout'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'payment_method' => 'qris',
        ]);

        $this->assertDatabaseHas('orders', [
            'source' => 'pos',
            'cashier_id' => $admin->id,
        ]);
    }
}
