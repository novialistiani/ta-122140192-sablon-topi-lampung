<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDropdown = false;
    public $guardType = 'web'; // 'web' for users, 'admin' for admins
    
    protected $notificationService;

    public function boot(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function mount($guard = 'web')
    {
        $this->guardType = $guard;
        $this->loadNotifications();
    }

    /**
     * Load notifications from database
     */
    public function loadNotifications()
    {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        // Get latest 10 notifications
        $this->notifications = $this->notificationService
            ->getNotifications($user, 10)
            ->toArray();

        // Get unread count
        $this->unreadCount = $this->notificationService
            ->getUnreadCount($user);
    }

    /**
     * Toggle dropdown visibility
     */
    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($notificationId)
    {
        $user = $this->getAuthenticatedUser();
        
        if ($user) {
            $this->notificationService->markAsRead($user, $notificationId);
            $this->loadNotifications();
            
            // Dispatch browser event for UI feedback
            $this->dispatch('notification-read', notificationId: $notificationId);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = $this->getAuthenticatedUser();
        
        if ($user) {
            $this->notificationService->markAllAsRead($user);
            $this->loadNotifications();
            
            // Dispatch browser event
            $this->dispatch('all-notifications-read');
        }
    }

    /**
     * Navigate to notification action URL
     */
    public function viewNotification($notificationId)
    {
        $this->markAsRead($notificationId);
        
        // Find notification action URL
        $notification = collect($this->notifications)
            ->firstWhere('id', $notificationId);
        
        if ($notification && !empty($notification['action_url'])) {
            return redirect($notification['action_url']);
        }
    }

    /**
     * Get authenticated user based on guard type
     */
    private function getAuthenticatedUser()
    {
        if ($this->guardType === 'admin') {
            return Auth::guard('admin')->user();
        }
        
        return Auth::guard('web')->user();
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.notification-bell');
    }

    /**
     * Polling: Auto-refresh notifications every 60 seconds
     * This will be triggered by wire:poll in the view
     */
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }
}
