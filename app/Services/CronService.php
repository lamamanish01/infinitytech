<?php

namespace App\Services;

use App\Models\CronJob;

class CronService
{
    public static function enabled($key)
    {
        return CronJob::where('key', $key)
            ->where('is_active', 1)
            ->exists();
    }

    public static function frequency($key)
    {
        return CronJob::where('key', $key)
            ->value('frequency');
    }
}
