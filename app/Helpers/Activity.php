<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class Activity
{
    public static function add($title, $message = null, $icon = null, $url = null)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => $title,
            'message' => $message,
            'icon' => $icon ?? 'fas fa-bell text-primary',
            'url' => $url,
        ]);
    }
}
