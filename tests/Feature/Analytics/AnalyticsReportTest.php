<?php

namespace Tests\Feature\Analytics;

use App\Models\Admin;
use App\Models\CustomDesignOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AnalyticsReportTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;
    protected string $today;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->today = now()->toDateString();
    }

    private function range(): array
    {
        return ['start_date' => $this->today, 'end_date' => $this->today];
    }

    public function test_guest_cannot_access_analytics_api(): void
    {
        $this->get(route('admin.api.analytics.sales-overview'))->assertRedirect();
    }

    public function test_sales_overview_combines_regular_and_custom_orders(): void
    {
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 100000,
        ]);
        CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total_price' => 100000,
        ]);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total' => 500000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.sales-overview', $this->range()));

        $response->assertOk();
        $this->assertEquals(300000, $response->json('data.totalRevenue'));
        $this->assertEquals(3, $response->json('data.completedOrders'));
        $this->assertEquals(4, $response->json('data.totalOrders'));
        // 3 selesai dari 4 pesanan = 75%
        $this->assertEquals(75, $response->json('data.conversionRate'));
        $this->assertEquals(100000, $response->json('data.averageOrderValue'));
    }

    public function test_sales_overview_excludes_orders_outside_date_range(): void
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 100000,
        ]);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 700000,
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.sales-overview', $this->range()));

        $this->assertEquals(100000, $response->json('data.totalRevenue'));
        $this->assertEquals(1, $response->json('data.completedOrders'));
    }

    public function test_sales_trend_merges_regular_and_custom_on_same_date(): void
    {
        Order::factory()->create([
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
            ->get(route('admin.api.analytics.sales-trend', $this->range()));

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(2, $response->json('data.0.orders'));
        $this->assertEquals(150000, $response->json('data.0.revenue'));
    }

    public function test_order_status_distribution_counts_both_order_types(): void
    {
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
        CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.order-status', $this->range()));

        $response->assertOk();
        $pending = collect($response->json('data'))->firstWhere('status', 'pending');
        $this->assertEquals(3, $pending['count']);
        $this->assertEquals('Menunggu', $pending['label']);
    }

    public function test_conversion_funnel_has_three_stages_with_rates(): void
    {
        // 3 pesanan reguler + 1 custom = 4 checkout
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        CustomDesignOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.conversion-funnel', $this->range()));

        $response->assertOk();
        $funnel = $response->json('data.funnel');

        $this->assertCount(3, $funnel);
        $this->assertEquals(4, $funnel[0]['count']);
        $this->assertEquals(100, $funnel[0]['rate']);
        $this->assertEquals(3, $funnel[1]['count']);
        $this->assertEquals(75, $funnel[1]['rate']);
        $this->assertEquals(2, $funnel[2]['count']);
        $this->assertEquals(50, $funnel[2]['rate']);
    }

    public function test_rfm_segment_champion_for_frequent_high_value_customer(): void
    {
        $champion = User::factory()->create(['name' => 'Pelanggan Champion']);
        Order::factory()->count(3)->create([
            'user_id' => $champion->id,
            'status' => 'completed',
            'total' => 200000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.customer', $this->range()));

        $response->assertOk();
        $row = collect($response->json('data.rfmTop'))
            ->firstWhere('customer', 'Pelanggan Champion');

        $this->assertEquals(3, $row['frequency']);
        $this->assertEquals(600000, $row['monetary']);
        $this->assertEquals('Champion', $row['segment']);
    }

    public function test_rfm_segment_at_risk_for_single_old_low_value_order(): void
    {
        $inactive = User::factory()->create(['name' => 'Pelanggan Lama']);
        Order::factory()->create([
            'user_id' => $inactive->id,
            'status' => 'completed',
            'total' => 50000,
            'created_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.customer', $this->range()));

        $row = collect($response->json('data.rfmTop'))
            ->firstWhere('customer', 'Pelanggan Lama');

        $this->assertEquals(1, $row['frequency']);
        $this->assertEquals('At Risk', $row['segment']);
    }

    public function test_customer_analytics_counts_new_and_active_customers(): void
    {
        Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.api.analytics.customer', $this->range()));

        $this->assertGreaterThanOrEqual(1, $response->json('data.newCustomers'));
        $this->assertEquals(1, $response->json('data.activeCustomers'));
    }

    public function test_analytics_report_can_be_exported_to_excel(): void
    {
        Excel::fake();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics.export', $this->range()));

        Excel::assertDownloaded('laporan-analytics-' . now()->format('Y-m-d') . '.xlsx');
    }
}