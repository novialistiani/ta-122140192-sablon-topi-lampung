<?php

namespace App\Http\Controllers;

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
     * Display all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // API request for dropdown
        if ($request->wantsJson() || $request->expectsJson()) {
            return $this->getNotificationsJson($user);
        }
        
        $filter = $request->get('filter', 'all'); // all, unread, read

        $query = $this->notificationService->getNotifications($user, null);

        // Apply filters
        if ($filter === 'unread') {
            $query = $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query = $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20);

        return view('notifications.index', compact('notifications', 'filter'));
    }
    
    /**
     * Get notifications as JSON for dropdown
     */
    protected function getNotificationsJson($user)
    {
        $notifications = $this->notificationService->getNotifications($user, 10)
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
        
        $unreadCount = $this->notificationService->getNotifications($user)
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
        $user = Auth::user();
        
        // Verify notification belongs to this user
        $notification = \App\Models\Notification::where('id', $id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $user->id)
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
        $user = Auth::user();
        $this->notificationService->markAllAsRead($user->id, 'user');

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Archive notification
     */
    public function archive($id)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        if ($notification) {
            $notification->archive();
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi diarsipkan');
    }
}
