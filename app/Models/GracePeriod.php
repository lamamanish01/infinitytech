<?php

namespace App\Models;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GracePeriod extends Model
{
    protected $fillable = [
        'customer_id',
        'grace_days',
        'grace_start',
        'grace_end'
    ];

    protected $casts = [
        'grace_start' => 'datetime',
        'grace_end' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
