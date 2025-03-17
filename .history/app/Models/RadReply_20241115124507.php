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

    public function authorizeSession(Customer $customer, Bandwidth $bandwidth)
    {
        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Framed-IP-Address',
            'op' => ':=',
            'value' => 'ip_address',
        ]);

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $bandwidth->upload_rate,
        ]);
    }
}

