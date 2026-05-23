<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsQueue extends Model
{
    protected $fillable = [
        'username',
        'mobile',
        'message',
        'type',
        'status',
        'retry_count',
        'send_at'
    ];

    protected $casts = [
        'send_at' => 'datetime'
    ];
}
