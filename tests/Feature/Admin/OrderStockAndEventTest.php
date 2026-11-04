<?php

namespace Tests\Feature\Admin;

use App\Events\CustomDesignApprovedEvent;
use App\Events\OrderApprovedEvent;
use App\Events\OrderRejectedEvent;
use App\Events\PaymentReceivedEvent;
use App\Mail\OrderApprovedMail;
use App\Mail\OrderRejectionMail;
use App\Models\Admin;
use App\Models\CustomDesignOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderStockAndEventTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    protected function makeOrder(Product $product, int $qty, array $override = []): Order
    {
        $user = User::factory()->create();

        return Order::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'items' => [[
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => 'Produk Uji',
                'price' => 50000,
                'quantity' => $qty,
            ]],
        ], $override));
    }

    public function test_approve_deducts_product_stock(): void
    {
        Mail::fake();
        Event::fake([OrderApprovedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->makeOrder($product, 3);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=regular');

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertEquals('approved', $order->fresh()->status);
    }

    public function test_approve_fails_when_stock_is_insufficient(): void
    {
        Mail::fake();
        Event::fake([OrderApprovedEvent::class]);

        $product = Product::factory()->create(['stock' => 2]);
        $order = $this->makeOrder($product, 3);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=regular');

        $response->assertSessionHas('error');
        $this->assertEquals(2, $product->fresh()->stock);
        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_approve_custom_order_deducts_stock(): void
    {
        Mail::fake();
        Event::fake([CustomDesignApprovedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = CustomDesignOrder::factory()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=custom');

        $this->assertEquals(8, $product->fresh()->stock);
        $this->assertEquals('approved', $order->fresh()->status);
    }

    public function test_reject_approved_order_restores_stock(): void
    {
        Mail::fake();
        Event::fake([OrderRejectedEvent::class]);

        $product = Product::factory()->create(['stock' => 7]);
        $order = $this->makeOrder($product, 3, ['status' => 'approved']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', [
                'reason' => 'Bahan sedang habis',
            ]);

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals('rejected', $order->fresh()->status);
    }

    public function test_reject_pending_order_does_not_change_stock(): void
    {
        Mail::fake();
        Event::fake([OrderRejectedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->makeOrder($product, 3, ['status' => 'pending']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', [
                'reason' => 'Desain tidak sesuai',
            ]);

        $this->assertEquals(10, $product->fresh()->stock);
    }

    public function test_cancelling_approved_order_via_update_status_restores_stock(): void
    {
        $product = Product::factory()->create(['stock' => 7]);
        $order = $this->makeOrder($product, 3, ['status' => 'approved']);

        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.order.update-status', $order->id) . '?type=regular', [
                'status' => 'cancelled',
            ]);

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_admin_can_reject_custom_order_with_reason(): void
    {
        Mail::fake();

        $order = CustomDesignOrder::factory()->create(['status' => 'pending']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=custom', [
                'reason' => 'Resolusi desain terlalu rendah',
            ]);

        $order = $order->fresh();
        $this->assertEquals('rejected', $order->status);
        $this->assertEquals('Resolusi desain terlalu rendah', $order->admin_notes);
    }

    public function test_approve_dispatches_order_approved_event(): void
    {
        Mail::fake();
        Event::fake([OrderApprovedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->makeOrder($product, 1);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=regular');

        Event::assertDispatched(OrderApprovedEvent::class);
    }

    public function test_approve_custom_order_dispatches_custom_design_event(): void
    {
        Mail::fake();
        Event::fake([CustomDesignApprovedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = CustomDesignOrder::factory()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=custom');

        Event::assertDispatched(CustomDesignApprovedEvent::class);
    }

    public function test_reject_dispatches_order_rejected_event(): void
    {
        Mail::fake();
        Event::fake([OrderRejectedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->makeOrder($product, 1);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', [
                'reason' => 'Stok tidak tersedia',
            ]);

        Event::assertDispatched(OrderRejectedEvent::class);
    }

    public function test_approve_sends_approval_email_to_customer(): void
    {
        Mail::fake();
        Event::fake([OrderApprovedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->makeOrder($product, 1);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=regular');

        Mail::assertSent(OrderApprovedMail::class);
    }

    public function test_reject_sends_rejection_email_to_customer(): void
    {
        Mail::fake();
        Event::fake([OrderRejectedEvent::class]);

        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->makeOrder($product, 1);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', [
                'reason' => 'Stok tidak tersedia',
            ]);

        Mail::assertSent(OrderRejectionMail::class);
    }

    public function test_mark_payment_received_dispatches_payment_event(): void
    {
        Event::fake([PaymentReceivedEvent::class]);

        $order = Order::factory()->create([
        'status' => 'approved',
        'payment_status' => 'pending_verification',
         'payment_proof' => 'payment-proofs/bukti.jpg',
    ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.mark-payment-received', $order->id) . '?type=regular');

        Event::assertDispatched(PaymentReceivedEvent::class);
    }
}