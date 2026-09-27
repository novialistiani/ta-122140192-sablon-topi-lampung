<?php

namespace Tests\Feature\Payment;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_payment_page_for_approved_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->get(route('pemesanan', ['order_type' => 'regular', 'order_id' => $order->id]));

        $response->assertOk();
    }

    public function test_customer_redirected_when_order_not_approved(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get(route('pemesanan', ['order_type' => 'regular', 'order_id' => $order->id]));

        $response->assertRedirect(route('order-list'));
        $response->assertSessionHas('error', 'Pesanan tidak ditemukan atau belum disetujui');
    }

    public function test_customer_can_submit_payment_proof_with_todays_pickup_date(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $file = UploadedFile::fake()->image('bukti-transfer.jpg');

        $this->actingAs($user)->post(route('pemesanan.submit-payment'), [
            'order_type' => 'regular',
            'order_id' => $order->id,
            'pickup_date' => now()->format('Y-m-d'),
            'payment_proof' => $file,
        ]);

        $this->assertEquals('pending_verification', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->payment_proof);
    }

    public function test_submit_payment_fails_with_past_pickup_date(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $file = UploadedFile::fake()->image('bukti-transfer.jpg');

        $response = $this->actingAs($user)->post(route('pemesanan.submit-payment'), [
            'order_type' => 'regular',
            'order_id' => $order->id,
            'pickup_date' => now()->subDays(1)->format('Y-m-d'),
            'payment_proof' => $file,
        ]);

        $response->assertSessionHasErrors('pickup_date');
    }

    public function test_submit_payment_fails_without_proof(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post(route('pemesanan.submit-payment'), [
            'order_type' => 'regular',
            'order_id' => $order->id,
            'pickup_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('payment_proof');
    }

    public function test_admin_can_mark_payment_as_received(): void
    {
        $admin = Admin::factory()->create();
        $order = Order::factory()->create([
            'status' => 'approved',
            'payment_status' => 'pending_verification',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.order.mark-payment-received', $order->id) . '?type=regular');

        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_admin_cannot_mark_already_paid_order_again(): void
    {
        $admin = Admin::factory()->create();
        $order = Order::factory()->create([
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.order.mark-payment-received', $order->id) . '?type=regular');

        $response->assertSessionHas('error', 'Pesanan sudah ditandai sebagai dibayar.');
    }
}