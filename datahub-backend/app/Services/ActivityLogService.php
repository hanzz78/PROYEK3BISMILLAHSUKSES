<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * Log a user activity.
     *
     * @param string $action
     * @param string $description
     * @param int $userId
     * @param Request $request
     * @return ActivityLog
     */
    public function log(string $action, string $description, int $userId, Request $request): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
        ]);
    }
}
