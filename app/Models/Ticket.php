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
        'ticket_no',
        'user_id',
        'assigned_to',
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

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'closed'); // adjust
    }

    public function scopePriorityOrdered($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high'   THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low'    THEN 4
                ELSE 5
            END DESC
        ");
    }
}
