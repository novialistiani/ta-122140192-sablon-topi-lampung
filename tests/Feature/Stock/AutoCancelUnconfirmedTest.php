<?php

namespace Tests\Feature\Stock;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoCancelUnconfirmedTest extends TestCase
{
    use RefreshDatabase;

    private function pendingOrder(string $deadline): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 20]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Hitam',
            'size' => 'L',
            'price' => 50000,
            'stock' => 10,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'confirmation_deadline' => $deadline === 'past' ? now()->subHour() : now()->addHour(),
            'items' => [[
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'name' => $product->name,
                'price' => 50000,
                'quantity' => 3,
            ]],
        ]);

        return [$order, $product, $variant];
    }

    public function test_unconfirmed_order_is_cancelled_without_changing_stock(): void
    {
        [$order, $product, $variant] = $this->pendingOrder('past');

        Artisan::call('orders:auto-cancel-unconfirmed');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(20, $product->fresh()->stock);
        $this->assertEquals(10, $variant->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_order_before_its_deadline_is_not_cancelled(): void
    {
        [$order] = $this->pendingOrder('future');

        Artisan::call('orders:auto-cancel-unconfirmed');

        $this->assertEquals('pending', $order->fresh()->status);
    }
}