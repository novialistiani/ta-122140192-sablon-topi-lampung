<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /**
     * Log an activity
     */
    public static function logActivity(
        string $action,
        ?string $description = null,
        ?array $properties = null,
        $subject = null
    ): ActivityLog {
        $user = self::getCurrentUser();
        
        return ActivityLog::create([
            'user_type' => $user['type'],
            'user_id' => $user['id'],
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description ?? self::generateDescription($action, $subject),
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get current authenticated user
     */
    protected static function getCurrentUser(): array
    {
        // Check admin guard first
        if (Auth::guard('admin')->check()) {
            return [
                'type' => 'App\Models\Admin',
                'id' => Auth::guard('admin')->id(),
            ];
        }

        // Check web guard
        if (Auth::guard('web')->check()) {
            return [
                'type' => 'App\Models\User',
                'id' => Auth::guard('web')->id(),
            ];
        }

        return [
            'type' => 'Guest',
            'id' => 0,
        ];
    }

    /**
     * Generate description for activity
     */
    protected static function generateDescription(string $action, $subject = null): string
    {
        $user = self::getCurrentUser();
        $userName = Auth::guard('admin')->user()?->name ?? Auth::guard('web')->user()?->name ?? 'Guest';
        
        if ($subject) {
            $subjectType = class_basename($subject);
            $subjectName = $subject->name ?? $subject->email ?? "#{$subject->id}";
            return "{$userName} {$action} {$subjectType}: {$subjectName}";
        }

        return "{$userName} {$action}";
    }

    /**
     * Get activity logs for this user
     */
    public function activities()
    {
        return ActivityLog::where('user_type', get_class($this))
                         ->where('user_id', $this->id)
                         ->orderBy('created_at', 'desc')
                         ->get();
    }

    /**
     * Get recent activity logs
     */
    public function recentActivities($limit = 10)
    {
        return ActivityLog::where('user_type', get_class($this))
                         ->where('user_id', $this->id)
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }
}
