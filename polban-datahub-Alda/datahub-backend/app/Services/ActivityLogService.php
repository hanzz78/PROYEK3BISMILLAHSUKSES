<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function log(string $action, string $description, int $userId, Request $request): ActivityLog
    {
        // Pastikan Model ActivityLog sudah punya $fillable
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action, // Pastikan panjang string tidak melebihi batas DB (biasanya 255)
            'description' => $description,
            'ip_address' => $request->ip(),
        ]);
    }
}