<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class LogActivity
{
    public static function log($action)
    {
        // Activity tracking currently disabled
        return;

        ActivityLog::create([
            'admin_id'   => auth('admin')->check() ? auth('admin')->user()->id : null,
            'action'     => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
