<?php

namespace Tests\Feature\Analytics;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\CustomDesignOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function total_revenue_can_be_calculated()
    {
        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 500000,
        ]);

        Order::factory()->count(1)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'total' => 300000,
        ]);

        $totalRevenue = Order::where('status', 'completed')->sum('total');

        $this->assertGreaterThan(0, $totalRevenue);
    }

    /** @test */
    public function order_count_by_status()
    {
        Order::factory()->count(3)->create(['status' => 'pending']);
        Order::factory()->count(5)->create(['status' => 'processing']);
        Order::factory()->count(2)->create(['status' => 'cancelled']);

        $pendingCount = Order::where('status', 'pending')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();

        $this->assertEquals(3, $pendingCount);
        $this->assertEquals(5, $processingCount);
        $this->assertEquals(2, $cancelledCount);
    }

    /** @test */
    public function average_order_value_calculated()
    {
        Order::factory()->count(2)->create(['user_id' => $this->user->id, 'total' => 100000]);
        Order::factory()->count(1)->create(['user_id' => $this->user->id, 'total' => 200000]);
        Order::factory()->count(1)->create(['user_id' => $this->user->id, 'total' => 300000]);

        $averageOrder = Order::avg('total');

        $this->assertGreaterThan(0, $averageOrder);
    }

    /** @test */
    public function customer_lifetime_value_tracked()
    {
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'completed', 'total' => 500000]);
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'completed', 'total' => 300000]);

        $lifetimeValue = Order::where('user_id', $this->user->id)
            ->where('status', 'completed')
            ->sum('total');

        $this->assertGreaterThan(0, $lifetimeValue);
    }

    /** @test */
    public function order_trend_by_date()
    {
        Order::factory()->count(5)->create(['created_at' => now()]);
        Order::factory()->count(3)->create(['created_at' => now()->subDay()]);

        $todayOrders = Order::whereDate('created_at', today())->count();
        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())->count();

        $this->assertEquals(5, $todayOrders);
        $this->assertEquals(3, $yesterdayOrders);
    }

    /** @test */
    public function repeat_customer_count()
    {
        $user2 = User::factory()->create();

        Order::factory()->count(2)->create(['user_id' => $this->user->id]);
        Order::factory()->count(1)->create(['user_id' => $user2->id]);

        // Count how many users have multiple orders
        $userOrderCounts = User::withCount('orders')->get();
        $repeatCustomers = $userOrderCounts->filter(function($user) {
            return $user->orders_count > 1;
        })->count();

        $this->assertGreaterThanOrEqual(1, $repeatCustomers);
    }

    /** @test */
    public function payment_status_distribution()
    {
        Order::factory()->count(4)->create(['payment_status' => 'paid']);
        Order::factory()->count(2)->create(['payment_status' => 'unpaid']);
        Order::factory()->count(1)->create(['payment_status' => 'failed']);

        $paidCount = Order::where('payment_status', 'paid')->count();
        $unpaidCount = Order::where('payment_status', 'unpaid')->count();
        $failedCount = Order::where('payment_status', 'failed')->count();

        $this->assertEquals(4, $paidCount);
        $this->assertEquals(2, $unpaidCount);
        $this->assertEquals(1, $failedCount);
    }

    /** @test */
    public function custom_design_order_statistics()
    {
        // Test separate CustomDesignOrder model instead of non-existent is_custom_design column
        Order::factory()->count(7)->create();
        CustomDesignOrder::factory()->count(3)->create();

        $regularOrderCount = Order::count();
        $customDesignCount = CustomDesignOrder::count();

        $this->assertEquals(7, $regularOrderCount);
        $this->assertEquals(3, $customDesignCount);
    }
}
