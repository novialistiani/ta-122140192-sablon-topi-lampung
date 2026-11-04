<?php

namespace Tests\Feature\Order;

use App\Models\CustomDesignOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'Pelanggan Invoice']);
    }

    private function paidOrder(array $overrides = []): Order
    {
        return Order::factory()->create(array_merge([
            'user_id' => $this->user->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'subtotal' => 204000,
            'total' => 204000,
            'items' => [[
                'product_id' => 1,
                'name' => 'Kaos Uji Invoice',
                'price' => 68000,
                'quantity' => 3,
                'color' => 'Hitam',
                'size' => 'L',
            ]],
        ], $overrides));
    }

    public function test_guest_cannot_open_invoice(): void
    {
        $order = $this->paidOrder();

        $this->get(route('order-invoice', ['type' => 'regular', 'id' => $order->id]))
            ->assertRedirect();
    }

    public function test_customer_can_view_invoice_of_paid_order(): void
    {
        $order = $this->paidOrder();

        $response = $this->actingAs($this->user)
            ->get(route('order-invoice', ['type' => 'regular', 'id' => $order->id]));

        $response->assertOk()
            ->assertSee('INVOICE')
            ->assertSee('LUNAS')
            ->assertSee('Pelanggan Invoice')
            ->assertSee('Kaos Uji Invoice');
    }

    public function test_invoice_shows_correct_total_and_invoice_number(): void
    {
        $order = $this->paidOrder();

        $response = $this->actingAs($this->user)
            ->get(route('order-invoice', ['type' => 'regular', 'id' => $order->id]));

        $response->assertSee('204.000')
            ->assertSee('INV-R' . str_pad($order->id, 5, '0', STR_PAD_LEFT));
    }

    public function test_invoice_is_not_available_for_unpaid_order(): void
    {
        $order = $this->paidOrder([
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'paid_at' => null,
        ]);

        $this->actingAs($this->user)
            ->get(route('order-invoice', ['type' => 'regular', 'id' => $order->id]))
            ->assertRedirect(route('order-detail', ['type' => 'regular', 'id' => $order->id]))
            ->assertSessionHas('error');
    }

    public function test_invoice_is_not_available_while_payment_awaits_verification(): void
    {
        $order = $this->paidOrder([
            'status' => 'approved',
            'payment_status' => 'pending_verification',
            'paid_at' => null,
        ]);

        $this->actingAs($this->user)
            ->get(route('order-invoice', ['type' => 'regular', 'id' => $order->id]))
            ->assertRedirect(route('order-detail', ['type' => 'regular', 'id' => $order->id]));
    }

    public function test_customer_cannot_view_invoice_of_another_customer(): void
    {
        $other = User::factory()->create();
        $order = $this->paidOrder(['user_id' => $other->id]);

        $this->actingAs($this->user)
            ->get(route('order-invoice', ['type' => 'regular', 'id' => $order->id]))
            ->assertNotFound();
    }

    public function test_customer_can_view_invoice_of_paid_custom_design_order(): void
    {
        $order = CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'product_price' => 150000,
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('order-invoice', ['type' => 'custom', 'id' => $order->id]));

        $response->assertOk()
            ->assertSee('Custom Design')
            ->assertSee('150.000')
            ->assertSee('INV-C' . str_pad($order->id, 5, '0', STR_PAD_LEFT));
    }

    public function test_invoice_button_is_shown_on_order_detail_only_when_paid(): void
    {
        $paid = $this->paidOrder();
        $unpaid = $this->paidOrder([
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'paid_at' => null,
        ]);

        $this->actingAs($this->user)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $paid->id]))
            ->assertOk()
            ->assertSee('Unduh Invoice');

        $this->actingAs($this->user)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $unpaid->id]))
            ->assertOk()
            ->assertDontSee('Unduh Invoice');
    }
}