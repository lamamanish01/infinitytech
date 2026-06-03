<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronJob extends Model
{
    protected $fillable = [
        'key',
        'name',
        'is_active',
        'last_run_at',
        'frequency',
    ];
}
