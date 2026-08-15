<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $adminId = auth('admin')->id();
        
        // Get filter from request
        $filter = $request->get('filter', 'all'); // all, unread, read
        
        // Get notifications based on filter
        $query = \App\Models\Notification::where('user_id', $adminId)
            ->orderBy('created_at', 'desc');
            
        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }
        
        $notifications = $query->paginate(20);
        
        // Get unread count
        $unreadCount = $this->notificationService->getUnreadCount($adminId);
        
        return view('admin.notifikasi', compact('notifications', 'unreadCount', 'filter'));
    }

    public function markAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);
        
        if (empty($notificationIds)) {
            return response()->json(['success' => false, 'message' => 'No notifications selected']);
        }
        
        $adminId = auth('admin')->id();
        
        \App\Models\Notification::whereIn('id', $notificationIds)
            ->where('user_id', $adminId)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        return response()->json(['success' => true, 'message' => 'Notifications marked as read']);
    }

    public function markAllAsRead()
    {
        $adminId = auth('admin')->id();
        
        \App\Models\Notification::where('user_id', $adminId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        
        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    }
}
