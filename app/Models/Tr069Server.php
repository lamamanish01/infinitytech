<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tr069Server extends Model
{
    protected $fillable = [
        'name',
        'acs_url',
        'acs_username',
        'acs_password',
        'status',
    ];

    public function devices()
    {
        return $this->hasMany(Tr069Device::class, 'tr069_server_id');
    }
}
