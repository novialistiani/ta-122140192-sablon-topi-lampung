<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getUserNotifications($userId, 10);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        // Transform for dropdown
        $transformed = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'priority' => $notification->priority,
                'read_at' => $notification->read_at,
                'action_url' => $notification->action_url,
                'action_text' => $notification->action_text,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $transformed,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $userId = Auth::id();
        $count = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = $this->notificationService->markAsRead($id);

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = Auth::id();
        $count = $this->notificationService->markAllAsRead($userId);

        return response()->json([
            'success' => true,
            'message' => "Semua notifikasi telah ditandai sebagai dibaca",
            'count' => $count,
        ]);
    }

    /**
     * Mark selected notifications as read
     */
    public function markSelectedAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id',
        ]);

        $userId = Auth::id();
        $count = $this->notificationService->markSelectedAsRead(
            $request->notification_ids,
            $userId
        );

        return response()->json([
            'success' => true,
            'message' => "{$count} notifikasi telah ditandai sebagai dibaca",
            'count' => $count,
        ]);
    }
}
