<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'auth_token',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
