<?php

namespace Tests\Unit\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function activity_log_can_be_created()
    {
        $log = ActivityLog::create([
            'user_type' => 'App\\Models\\User',
            'user_id' => $this->user->id,
            'action' => 'create',
            'subject_type' => 'Product',
            'subject_id' => 1,
            'description' => 'Created new product',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create',
            'subject_type' => 'Product',
        ]);
    }

    /** @test */
    public function activity_log_tracks_user()
    {
        $log = ActivityLog::create([
            'user_type' => 'App\\Models\\User',
            'user_id' => $this->user->id,
            'action' => 'update',
            'subject_type' => 'Order',
            'subject_id' => 5,
            'description' => 'Updated order status',
        ]);

        $this->assertTrue($log->user->is($this->user));
    }

    /** @test */
    public function activity_log_records_action_types()
    {
        $actions = ['create', 'update', 'delete', 'view', 'export'];

        foreach ($actions as $action) {
            ActivityLog::create([
                'user_id' => $this->user->id,
                'action' => $action,
                'subject_type' => 'Test',
                'subject_id' => 1,
                'description' => 'Test ' . $action,
            ]);
        }

        $this->assertEquals(5, ActivityLog::count());
    }

    /** @test */
    public function activity_log_can_have_changes()
    {
        $changes = [
            'status' => ['pending', 'completed'],
            'price' => [100000, 150000],
        ];

        $log = ActivityLog::create([
            'user_id' => $this->user->id,
            'action' => 'update',
            'subject_type' => 'Order',
            'subject_id' => 1,
            'description' => 'Order updated',
            'properties' => $changes,
        ]);

        $this->assertIsArray($log->properties);
    }

    /** @test */
    public function activity_log_has_timestamp()
    {
        $log = ActivityLog::create([
            'user_id' => $this->user->id,
            'action' => 'view',
            'subject_type' => 'Report',
            'subject_id' => 1,
            'description' => 'Viewed report',
        ]);

        $this->assertNotNull($log->created_at);
    }

    /** @test */
    public function activity_log_can_be_filtered_by_model()
    {
        ActivityLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
            'subject_type' => 'Product',
            'subject_id' => 1,
        ]);

        ActivityLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
            'subject_type' => 'Order',
            'subject_id' => 1,
        ]);

        $products = ActivityLog::where('subject_type', 'Product')->get();
        $this->assertEquals(1, $products->count());
    }

    /** @test */
    public function activity_log_can_be_filtered_by_user()
    {
        $user2 = User::factory()->create();

        ActivityLog::create([
            'user_id' => $this->user->id,
            'action' => 'update',
            'subject_type' => 'Order',
            'subject_id' => 1,
        ]);

        ActivityLog::create([
            'user_id' => $user2->id,
            'action' => 'update',
            'subject_type' => 'Order',
            'subject_id' => 2,
        ]);

        $userLogs = ActivityLog::where('user_id', $this->user->id)->get();
        $this->assertEquals(1, $userLogs->count());
    }

    /** @test */
    public function activity_log_can_be_exported()
    {
        for ($i = 0; $i < 10; $i++) {
            ActivityLog::create([
                'user_id' => $this->user->id,
                'action' => 'export',
                'subject_type' => 'Report',
                'subject_id' => $i + 1,
                'description' => 'Exported report ' . ($i + 1),
            ]);
        }

        $logs = ActivityLog::where('action', 'export')->get();
        $this->assertEquals(10, $logs->count());
    }

    /** @test */
    public function activity_log_stores_description()
    {
        $description = 'User created order #ORD-2024-001 with total Rp 500,000';

        $log = ActivityLog::create([
            'user_id' => $this->user->id,
            'action' => 'create',
            'subject_type' => 'Order',
            'subject_id' => 1,
            'description' => $description,
        ]);

        $this->assertEquals($description, $log->fresh()->description);
    }
}
