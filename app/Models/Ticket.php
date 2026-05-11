<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'subject',
        'message',
        'priority',
        'status'
    ];

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
