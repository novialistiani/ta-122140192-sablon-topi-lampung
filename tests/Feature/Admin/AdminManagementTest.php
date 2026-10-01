<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create(['email' => 'admin@example.com']);
    }

    public function test_admin_can_view_order_list(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.order-list'));

        $response->assertOk();
    }

    public function test_guest_cannot_view_order_list(): void
    {
        $response = $this->get(route('admin.order-list'));

        $response->assertRedirect();
    }

    public function test_admin_can_approve_pending_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.approve', $order->id) . '?type=regular');

        $order = $order->fresh();
        $this->assertEquals('approved', $order->status);
        $this->assertNotNull($order->approved_at);
        $this->assertNotNull($order->payment_deadline);
    }

    public function test_reject_order_requires_reason(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', []);

        $response->assertSessionHasErrors('reason');
        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_admin_can_reject_order_with_reason(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.order.reject', $order->id) . '?type=regular', [
                'reason' => 'Desain tidak sesuai ketentuan',
            ]);

        $order = $order->fresh();
        $this->assertEquals('rejected', $order->status);
        $this->assertEquals('Desain tidak sesuai ketentuan', $order->admin_notes);
    }

    public function test_cannot_set_processing_when_order_unpaid(): void
    {
        $order = Order::factory()->create([
            'status' => 'approved',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.order.update-status', $order->id) . '?type=regular', [
                'status' => 'processing',
            ]);

        $this->assertNotEquals('processing', $order->fresh()->status);
    }

    public function test_can_set_processing_when_order_paid(): void
    {
        $order = Order::factory()->create([
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.order.update-status', $order->id) . '?type=regular', [
                'status' => 'processing',
            ]);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_overdue_badge_is_shown_on_order_list(): void
    {
        Order::factory()->create([
            'status' => 'approved',
            'pickup_date' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.order-list'));

        $response->assertOk()->assertSee('Terlambat');
    }

    public function test_overdue_badge_is_hidden_when_order_completed(): void
    {
        Order::factory()->create([
            'status' => 'completed',
            'pickup_date' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.order-list'));

        $response->assertOk()->assertDontSee('Terlambat');
    }

    public function test_expired_unpaid_order_is_auto_cancelled(): void
    {
        $order = Order::factory()->create([
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'payment_deadline' => now()->subMinutes(5),
        ]);

        Artisan::call('orders:cancel-expired');

        $this->assertNotEquals('approved', $order->fresh()->status);
    }

    public function test_expired_order_with_payment_proof_is_not_auto_cancelled(): void
    {
        $order = Order::factory()->create([
            'status' => 'approved',
            'payment_status' => 'pending_verification',
            'payment_deadline' => now()->subMinutes(5),
        ]);

        Artisan::call('orders:cancel-expired');

        $this->assertEquals('approved', $order->fresh()->status);
    }
        public function test_can_set_completed_when_order_paid(): void
    {
        $order = Order::factory()->create([
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.order.update-status', $order->id) . '?type=regular', [
                'status' => 'completed',
            ]);

        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_admin_can_view_order_history_page(): void
    {
        Order::factory()->create([
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.order-history'));

        $response->assertOk();
    }
}
