<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Activity Logging Service
 * 
 * Logs user actions for audit trails and security monitoring
 */
class ActivityLoggingService
{
    /**
     * Log a user activity
     */
    public function log(
        string $action,
        ?string $details = null,
        ?int $userId = null
    ): ActivityLog {
        $userId = $userId ?? Auth::id();

        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log authentication action
     */
    public function logAuth(string $type, ?int $userId = null, ?string $details = null): ActivityLog
    {
        return $this->log("auth:{$type}", $details, $userId);
    }

    /**
     * Log profile update
     */
    public function logProfileUpdate(array $changes): ActivityLog
    {
        return $this->log('profile:update', json_encode($changes));
    }

    /**
     * Log password change
     */
    public function logPasswordChange(): ActivityLog
    {
        return $this->log('auth:password_change');
    }

    /**
     * Log avatar upload
     */
    public function logAvatarUpload(string $fileName): ActivityLog
    {
        return $this->log('profile:avatar_upload', $fileName);
    }

    /**
     * Log avatar deletion
     */
    public function logAvatarDeletion(): ActivityLog
    {
        return $this->log('profile:avatar_delete');
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(string $type, string $details): ActivityLog
    {
        return $this->log("security:suspicious_activity", json_encode([
            'type' => $type,
            'details' => $details,
        ]));
    }

    /**
     * Get user activity log
     */
    public function getUserActivity(int $userId, int $limit = 50)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent suspicious activities
     */
    public function getSuspiciousActivities(int $limit = 100)
    {
        return ActivityLog::where('action', 'like', 'security:%')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
