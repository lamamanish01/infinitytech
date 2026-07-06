<?php

namespace App\Models;

use App\Models\Tr069Device;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tr069Server extends Model
{
    use SoftDeletes;

    protected $table = 'tr069_servers';

    protected $fillable = ['name', 'acs_url', 'acs_username', 'acs_password', 'status'];

    public function baseUrl(): string
    {
        return rtrim($this->acs_url, '/');
    }

    public function devices()
    {
        return $this->hasMany(Tr069Device::class, 'tr069_server_id');
    }
}
