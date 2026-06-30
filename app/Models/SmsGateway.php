<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = ['name', 'api_url', 'auth_token', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
