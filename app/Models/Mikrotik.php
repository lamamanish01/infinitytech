<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mikrotik extends Model
{
    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'is_active',
        'remarks',
    ];
}
