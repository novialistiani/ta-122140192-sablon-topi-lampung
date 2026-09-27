<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Notification;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendEmailNotificationJob;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    public function test_send_creates_notification_record(): void
    {
        $user = User::factory()->create();

        $notification = $this->service->send(
            'order_approved',
            $user,
            [
                'order_number' => 'ORD-001',
                'total_amount' => 'Rp 100.000',
            ],
            'high',
            false
        );

        $this->assertNotNull($notification);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'type' => 'order_approved',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'priority' => 'high',
        ]);
    }

    public function test_send_uses_default_title_and_message_when_not_provided(): void
    {
        $user = User::factory()->create();

        $notification = $this->service->send('order_completed', $user, []);

        $this->assertEquals('Pesanan Selesai', $notification->title);
    }

    public function test_send_with_email_flag_queues_email_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->service->send(
            'order_approved',
            $user,
            ['order_number' => 'ORD-002'],
            'high',
            true
        );

        Queue::assertPushed(SendEmailNotificationJob::class);
    }

    public function test_send_without_email_flag_does_not_queue_email_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->service->send('order_completed', $user, [], 'medium', false);

        Queue::assertNotPushed(SendEmailNotificationJob::class);
    }

    public function test_notify_order_status_update_creates_notification_with_correct_message(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $notification = $this->service->notifyOrderStatusUpdate(
            $order,
            $user->id,
            'processing',
            'completed'
        );

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Sedang Diproses', $notification->message);
        $this->assertStringContainsString('Selesai', $notification->message);
        $this->assertEquals('order_status_update', $notification->type);
    }

    public function test_notify_payment_received_creates_notification(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $notification = $this->service->notifyPaymentReceived($order, 'regular');

        $this->assertNotNull($notification);
        $this->assertEquals('payment_received', $notification->type);
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'type' => 'payment_received',
        ]);
    }

    public function test_get_unread_count_returns_correct_number(): void
    {
        $user = User::factory()->create();

        $this->service->send('order_approved', $user, [], 'high', false);
        $this->service->send('order_completed', $user, [], 'medium', false);

        $count = $this->service->getUnreadCount($user->id, 'user');

        $this->assertEquals(2, $count);
    }

    public function test_get_unread_count_excludes_chat_reply_type(): void
    {
        $user = User::factory()->create();

        $this->service->send('order_approved', $user, [], 'high', false);
        $this->service->notifyCustomerChatReply(1, $user->id, 'Admin Test');

        $count = $this->service->getUnreadCount($user->id, 'user');

        $this->assertEquals(1, $count);
    }

    public function test_mark_as_read_updates_read_at(): void
    {
        $user = User::factory()->create();
        $notification = $this->service->send('order_approved', $user, [], 'high', false);

        $this->assertNull($notification->fresh()->read_at);

        $this->service->markAsRead($notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_updates_all_unread_notifications(): void
    {
        $user = User::factory()->create();

        $this->service->send('order_approved', $user, [], 'high', false);
        $this->service->send('order_completed', $user, [], 'medium', false);

        $this->service->markAllAsRead($user->id, 'user');

        $unreadCount = $this->service->getUnreadCount($user->id, 'user');
        $this->assertEquals(0, $unreadCount);
    }

    public function test_notify_admin_new_order_sends_to_all_active_admins(): void
    {
        $activeAdmin = Admin::factory()->create(['status' => 'active']);
        $inactiveAdmin = Admin::factory()->create(['status' => 'inactive']);
        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $this->service->notifyAdminNewOrder($order, $customer);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => Admin::class,
            'notifiable_id' => $activeAdmin->id,
            'type' => 'new_order',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => Admin::class,
            'notifiable_id' => $inactiveAdmin->id,
            'type' => 'new_order',
        ]);
    }
}