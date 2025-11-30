<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tr069Server extends Model
{
    protected $fillable = ['name', 'ip', 'web_port', 'api_port'];
}
