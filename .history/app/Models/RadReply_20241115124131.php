<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadReply extends Model
{
    protected $table = "radreply";
    protected $guarded = [];
    public $timestamps = false;

    public function customer()
    {
        return $this->belongTo(Customer::class, 'username', 'username');
    }

    public function authorizeSession()
    {
        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Framed-IP-Address',
            'op' => ':=',
            'value' => 
        ]);
    }
}

