<?php

namespace Tests\Feature\Order;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_own_order_detail(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $order->id]));

        $response->assertOk();
    }

    public function test_order_detail_shows_approved_status_label(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $order->id]));

        $response->assertOk()->assertSee('Disetujui');
    }

    public function test_order_detail_shows_paid_status_label_when_payment_status_is_paid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'payment_status' => 'paid',
            'approved_at' => now(),
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $order->id]));

        $response->assertOk()->assertSee('Pembayaran Sudah Dikonfirmasi');
    }

    public function test_order_detail_shows_completed_status_label(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($user)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $order->id]));

        $response->assertOk();
    }

    public function test_guest_cannot_view_order_detail(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('order-detail', ['type' => 'regular', 'id' => $order->id]));

        $response->assertRedirect();
    }

    public function test_customer_cannot_view_other_customers_order_detail(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)
            ->get(route('order-detail', ['type' => 'regular', 'id' => $order->id]));

        $response->assertStatus(404);
    }

   
}