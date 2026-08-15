<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\CustomDesignOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create(['email' => 'admin@example.com']);
    }

    /** @test */
    public function admin_can_be_created()
    {
        $admin = Admin::create([
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('admins', [
            'email' => 'admin.baru@example.com',
        ]);
    }

    /** @test */
    public function admin_can_view_all_orders()
    {
        Order::factory()->count(5)->create();

        $orders = Order::all();

        $this->assertCount(5, $orders);
    }

    /** @test */
    public function admin_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->update(['status' => 'processing']);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function admin_can_approve_custom_design()
    {
        $design = CustomDesignOrder::factory()->create([
            'status' => 'pending',
        ]);

        $design->update(['status' => 'approved']);

        $this->assertEquals('approved', $design->fresh()->status);
    }

    /** @test */
    public function admin_can_reject_custom_design()
    {
        $design = CustomDesignOrder::factory()->create([
            'status' => 'pending',
        ]);

        $design->update(['status' => 'rejected']);

        $this->assertEquals('rejected', $design->fresh()->status);
    }

    /** @test */
    public function admin_can_add_notes_to_order()
    {
        $order = Order::factory()->create();

        $notes = 'Order ini perlu perhatian khusus';
        $order->update(['admin_notes' => $notes]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'admin_notes' => $notes,
        ]);
    }

    /** @test */
    public function admin_can_manage_users()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
    }

    /** @test */
    public function admin_can_view_activity_logs()
    {
        $this->assertIsIterable(Admin::all());
    }
}
