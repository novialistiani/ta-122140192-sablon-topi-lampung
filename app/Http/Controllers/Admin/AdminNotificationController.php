<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display all notifications for authenticated admin
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // API request for dropdown
        if ($request->wantsJson() || $request->expectsJson()) {
            return $this->getNotificationsJson($admin);
        }
        
        $filter = $request->get('filter', 'all'); // all, unread, read

        $query = $this->notificationService->getNotifications($admin, null);

        // Apply filters
        if ($filter === 'unread') {
            $query = $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query = $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20);

        return view('admin.notifications.index', compact('notifications', 'filter'));
    }
    
    /**
     * Get notifications as JSON for dropdown
     */
    protected function getNotificationsJson($admin)
    {
        $notifications = $this->notificationService->getNotifications($admin, 10)
            ->get()
            ->map(function ($notification) {
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
        
        $unreadCount = $this->notificationService->getNotifications($admin)
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $admin = Auth::guard('admin')->user();
        
        // Verify notification belongs to this admin
        $notification = \App\Models\Notification::where('id', $id)
            ->where('notifiable_type', 'App\\Models\\Admin')
            ->where('notifiable_id', $admin->id)
            ->first();
            
        if ($notification) {
            $this->notificationService->markAsRead($id);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $admin = Auth::guard('admin')->user();
        $this->notificationService->markAllAsRead($admin->id, 'admin');

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }
}
