<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\Admin;
use App\Jobs\SendEmailNotificationJob;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to a single recipient (SIMPLIFIED - always creates notification)
     * 
     * @param string $type - notification type
     * @param User|Admin $recipient - the recipient model
     * @param array $data - notification data including title, message, action_url
     * @param string $priority - low, medium, high, urgent
     * @param bool $sendEmail - whether to send email notification
     */
    public function send(string $type, $recipient, array $data = [], string $priority = 'medium', bool $sendEmail = false): ?Notification
    {
        try {
            // Determine notifiable type
            $notifiableType = get_class($recipient);
            
            // Get title and message from data or use defaults
            $title = $data['title'] ?? $this->getDefaultTitle($type);
            $message = $data['message'] ?? $this->getDefaultMessage($type, $data);
            $actionUrl = $data['action_url'] ?? null;
            $actionText = $data['action_text'] ?? 'Lihat Detail';

            // Create notification in database
            $notification = Notification::create([
                'type' => $type,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $recipient->id,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl,
                'action_text' => $actionText,
                'priority' => $priority,
            ]);

            Log::info('Notification created', [
                'id' => $notification->id,
                'type' => $type,
                'recipient' => $notifiableType . '#' . $recipient->id,
            ]);

            // Send email if enabled
            if ($sendEmail && $recipient->email) {
                $this->queueEmailSimple($notification, $recipient, $data);
            }

            return $notification;

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'type' => $type,
                'recipient_id' => $recipient->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get default title based on notification type
     */
    protected function getDefaultTitle(string $type): string
    {
        $titles = [
            'order_created' => 'Pesanan Berhasil Dibuat',
            'new_order_admin' => 'Pesanan Baru Masuk',
            'order_approved' => 'Pesanan Disetujui',
            'order_rejected' => 'Pesanan Ditolak',
            'order_completed' => 'Pesanan Selesai',
            'payment_received' => 'Pembayaran Diterima',
            'custom_design_uploaded' => 'Design Custom Diunggah',
            'new_custom_design_admin' => 'Design Custom Baru',
            'custom_design_approved' => 'Design Custom Disetujui',
            'custom_design_rejected' => 'Design Custom Ditolak',
        ];
        
        return $titles[$type] ?? 'Notifikasi';
    }

    /**
     * Get default message based on type and data
     */
    protected function getDefaultMessage(string $type, array $data): string
    {
        $orderNumber = $data['order_number'] ?? $data['design_number'] ?? '';
        $customerName = $data['customer_name'] ?? '';
        $totalAmount = $data['total_amount'] ?? '';
        $designName = $data['design_name'] ?? 'Desain Custom';
        $rejectionReason = $data['rejection_reason'] ?? '';
        
        $messages = [
            'order_created' => "Pesanan {$orderNumber} berhasil dibuat dengan total {$totalAmount}",
            'new_order_admin' => "Pesanan baru {$orderNumber} dari {$customerName} dengan total {$totalAmount}",
            'order_approved' => "Pesanan {$orderNumber} Anda telah disetujui dan sedang diproses",
            'order_rejected' => "Pesanan {$orderNumber} Anda ditolak. " . ($data['reason'] ?? ''),
            'order_completed' => "Pesanan {$orderNumber} telah selesai",
            'payment_received' => "Pembayaran untuk pesanan {$orderNumber} telah diterima",
            'custom_design_uploaded' => "Design custom untuk pesanan telah diunggah",
            'new_custom_design_admin' => "Design custom baru dari {$customerName} untuk {$designName}",
            'custom_design_approved' => "Design custom {$orderNumber} Anda telah disetujui. Silakan lakukan pembayaran.",
            'custom_design_rejected' => "Design custom {$orderNumber} Anda ditolak. {$rejectionReason}",
        ];
        
        return $messages[$type] ?? 'Ada notifikasi baru untuk Anda';
    }

    /**
     * Send notification to multiple recipients
     */
    public function sendToMany(string $type, $recipients, array $data = [], string $priority = 'medium', bool $sendEmail = false): array
    {
        $notifications = [];
        
        foreach ($recipients as $recipient) {
            $notification = $this->send($type, $recipient, $data, $priority, $sendEmail);
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * Simple email queue (without template dependency)
     */
    protected function queueEmailSimple(Notification $notification, $recipient, array $data): void
    {
        try {
            // Create notification log
            $log = NotificationLog::create([
                'notification_id' => $notification->id,
                'channel' => 'email',
                'recipient_type' => get_class($recipient),
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'subject' => $notification->title,
                'status' => 'pending',
            ]);

            // Dispatch email job
            SendEmailNotificationJob::dispatch($log->id, $data);

            Log::info('Email notification queued', [
                'notification_id' => $notification->id,
                'recipient_email' => $recipient->email,
                'type' => $notification->type,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create email log', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Replace placeholders in template
     */
    protected function replacePlaceholders(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $template = str_replace("{{$key}}", $value, $template);
                $template = str_replace("{{ $key }}", $value, $template);
            }
        }
        return $template;
    }

    /**
     * Create a new notification (legacy method)
     */
    public function create(array $data)
    {
        return Notification::create($data);
    }

    /**
     * Notify when order is approved (to User)
     */
    public function notifyOrderApproved($order, $userId)
    {
        $orderType = get_class($order) === 'App\Models\CustomDesignOrder' ? 'Custom Design' : 'Regular';
        
        return $this->create([
            'type' => 'order_approved',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'title' => 'Pesanan Disetujui',
            'message' => "Pesanan {$orderType} #{$order->id} Anda telah disetujui! Pesanan sedang diproses.",
            'data' => [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'status' => 'approved',
            ],
        ]);
    }

    /**
     * Notify when order is rejected (to User)
     */
    public function notifyOrderRejected($order, $userId, $reason = null)
    {
        $orderType = get_class($order) === 'App\Models\CustomDesignOrder' ? 'Custom Design' : 'Regular';
        $message = "Pesanan {$orderType} #{$order->id} Anda ditolak.";
        if ($reason) {
            $message .= " Alasan: {$reason}";
        }
        
        return $this->create([
            'type' => 'order_rejected',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'title' => 'Pesanan Ditolak',
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'status' => 'rejected',
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Notify when order status is updated (to User)
     */
    public function notifyOrderStatusUpdate($order, $userId, $oldStatus, $newStatus)
    {
        $orderType = get_class($order) === 'App\Models\CustomDesignOrder' ? 'Custom Design' : 'Regular';
        $statusLabels = [
            'pending' => 'Menunggu Konfirmasi',
            'approved' => 'Disetujui',
            'processing' => 'Sedang Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Ditolak',
        ];
        
        return $this->create([
            'type' => 'order_status_update',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'title' => 'Status Pesanan Diperbarui',
            'message' => "Pesanan {$orderType} #{$order->id} diperbarui dari {$statusLabels[$oldStatus]} menjadi {$statusLabels[$newStatus]}.",
            'data' => [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);
    }

    /**
     * Notify when payment is received (to User)
     */
    public function notifyPaymentReceived($order, $orderType = 'Regular')
    {
        // Determine order type if not provided
        if ($orderType === 'Regular' || $orderType === 'regular') {
            $orderType = 'Regular';
        } elseif ($orderType === 'CustomDesign' || $orderType === 'custom_design') {
            $orderType = 'Custom Design';
        }
        
        return $this->create([
            'type' => 'payment_received',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $order->customer_id,
            'title' => 'Pembayaran Diterima',
            'message' => "Pembayaran untuk pesanan {$orderType} #{$order->id} telah diterima. Pesanan akan segera diproses.",
            'data' => [
                'order_id' => $order->id,
                'order_type' => $orderType,
                'order_number' => $order->order_number ?? "ORD-{$order->id}",
                'payment_status' => 'paid',
            ],
        ]);
    }

    /**
     * Notify admin when new order is created (to Admins)
     */
    public function notifyAdminNewOrder($order, $customer)
    {
        $orderType = get_class($order) === 'App\Models\CustomDesignOrder' ? 'Custom Design' : 'Regular';
        $admins = Admin::where('status', 'active')->get();
        
        foreach ($admins as $admin) {
            $this->create([
                'type' => 'new_order',
                'notifiable_type' => 'App\\Models\\Admin',
                'notifiable_id' => $admin->id,
                'title' => 'Pesanan Baru',
                'message' => "Pesanan {$orderType} baru #{$order->id} dari {$customer->name}.",
                'data' => [
                    'order_id' => $order->id,
                    'order_type' => $orderType,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                ],
            ]);
        }
    }

    /**
     * Notify admin when VA is activated (to Admins)
     */
    public function notifyAdminVAActivated($order, $vaNumber)
    {
        $orderType = get_class($order) === 'App\Models\CustomDesignOrder' ? 'Custom Design' : 'Regular';
        $admins = Admin::where('status', 'active')->get();
        
        foreach ($admins as $admin) {
            $this->create([
                'type' => 'va_activated',
                'notifiable_type' => 'App\\Models\\Admin',
                'notifiable_id' => $admin->id,
                'title' => 'Virtual Account Aktif',
                'message' => "VA #{$vaNumber} untuk pesanan {$orderType} #{$order->id} telah diaktifkan.",
                'data' => [
                    'order_id' => $order->id,
                    'order_type' => $orderType,
                    'va_number' => $vaNumber,
                ],
            ]);
        }
    }

    /**
     * Notify customer when admin replies to chat (to User)
     */
    public function notifyCustomerChatReply($conversationId, $customerId, $adminName)
    {
        return $this->create([
            'type' => 'chat_reply',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $customerId,
            'title' => 'Balasan Baru',
            'message' => "{$adminName} membalas chat Anda.",
            'data' => [
                'conversation_id' => $conversationId,
                'admin_name' => $adminName,
            ],
        ]);
    }

    /**
     * Get unread count for user
     * Note: Chat notifications (type='chat_reply') are excluded as they use badge system instead
     */
    public function getUnreadCount($userId, $type = 'user')
    {
        if ($type === 'admin') {
            return Notification::forAdmin($userId)->unread()->count();
        }
        // Exclude chat notifications from unread count
        return Notification::forUser($userId)
            ->unread()
            ->where('type', '!=', 'chat_reply')
            ->count();
    }

    /**
     * Get notifications query builder for user or admin
     * Used by controllers for pagination
     */
    public function getNotifications($recipient, $limit = null)
    {
        $notifiableType = get_class($recipient);
        
        $query = Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $recipient->id)
            ->orderBy('created_at', 'desc');
            
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query;
    }

    /**
     * Get notifications for user or admin (returns collection)
     * Note: Chat notifications (type='chat_reply') are excluded as they use badge system instead
     */
    public function getUserNotifications($userId, $limit = 20, $type = 'user')
    {
        $query = $type === 'admin' 
            ? Notification::forAdmin($userId) 
            : Notification::forUser($userId);
        
        // Exclude chat notifications - they should only appear as badge on chat icon
        $query->where('type', '!=', 'chat_reply');
            
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        return $notification;
    }

    /**
     * Mark all notifications as read for user or admin
     */
    public function markAllAsRead($userId, $type = 'user')
    {
        $query = $type === 'admin' 
            ? Notification::forAdmin($userId) 
            : Notification::forUser($userId);
            
        return $query->unread()
            ->update([
                'read_at' => now(),
            ]);
    }

    /**
     * Mark selected notifications as read
     */
    public function markSelectedAsRead(array $notificationIds, $userId, $type = 'user')
    {
        $query = Notification::whereIn('id', $notificationIds);
        
        if ($type === 'admin') {
            $query->forAdmin($userId);
        } else {
            $query->forUser($userId);
        }
        
        return $query->unread()
            ->update([
                'read_at' => now(),
            ]);
    }
}
