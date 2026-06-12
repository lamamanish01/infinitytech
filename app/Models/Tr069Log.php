<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tr069Log extends Model
{
    protected $table = 'tr069_logs';

    protected $fillable = [
        'tr069_device_id', 'action', 'status', 'request_payload', 'response_payload', 'message'
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(Tr069Device::class, 'tr069_device_id');
    }
}
