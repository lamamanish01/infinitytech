<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tr069Device extends Model
{
    protected $fillable = [
        'tr069_server_id',
        'customer_id',
        'serial_number',
        'username',
        'oui',
        'product_class',
        'manufacturer',
        'model',
        'mac_address',
        'ip_address',
        'last_inform',
        'status',
    ];

    protected $casts = [
        'last_inform' => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(Tr069Server::class, 'tr069_server_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isOnline(): bool
    {
        return $this->last_inform
            ? $this->last_inform->diffInMinutes(now()) < 10
            : false;
    }
}
