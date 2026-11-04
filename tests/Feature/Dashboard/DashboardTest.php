<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use App\Models\CustomDesignOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_dashboard_stats(): void
    {
        $this->get(route('api.dashboard.stats'))->assertRedirect();
    }

    public function test_admin_can_view_dashboard_page(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_stats_combine_regular_and_custom_completed_orders(): void
    {
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 100000,
        ]);
        CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total_price' => 50000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('api.dashboard.stats'));

        $response->assertOk();
        $this->assertEquals(3, $response->json('total_sold'));
        $this->assertEquals(250000, $response->json('revenue'));
    }

    public function test_revenue_only_counts_completed_orders(): void
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 100000,
        ]);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total' => 900000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('api.dashboard.stats'));

        $this->assertEquals(100000, $response->json('revenue'));
        $this->assertEquals(1, $response->json('pending_orders'));
    }

    public function test_stats_count_registered_customers(): void
    {
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('api.dashboard.stats'));

        $this->assertEquals(User::count(), $response->json('customers'));
    }

    public function test_sales_data_returns_chart_structure(): void
    {
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('api.dashboard.sales'));

        $response->assertOk()->assertJsonStructure([
            'labels',
            'datasets' => [['label', 'data']],
        ]);
        $this->assertEquals(2, array_sum($response->json('datasets.0.data')));
    }

    public function test_recent_orders_merge_regular_and_custom_with_limit(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);
        CustomDesignOrder::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('api.dashboard.orders', ['limit' => 4]));

        $response->assertOk();
        $this->assertCount(4, $response->json());
    }

    public function test_top_products_counts_variant_occurrences(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Hitam',
            'size' => 'L',
            'price' => 100000,
            'stock' => 10,
        ]);

        $items = [[
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'name' => $product->name,
            'price' => 100000,
            'quantity' => 1,
        ]];

        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'items' => $items,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('api.dashboard.products'));

        $response->assertOk();
        $this->assertEquals($product->id, $response->json('0.id'));
        $this->assertEquals(2, $response->json('0.order_count'));
    }

    public function test_sold_trend_is_zero_without_previous_month_data(): void
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'completed_at' => now()->startOfMonth()->addSecond(),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertViewHas('soldTrend', 0);
    }

    public function test_sold_trend_is_calculated_against_previous_month(): void
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'completed_at' => now()->subMonth()->startOfMonth()->addDay(),
        ]);
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'completed_at' => now()->startOfMonth()->addSecond(),
        ]);

        // 1 pesanan bulan lalu, 2 pesanan bulan ini => naik 100%
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertViewHas('soldTrend', 100.0);
    }
}