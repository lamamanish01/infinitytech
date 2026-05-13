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
    ];

    protected $casts = [
        'grace_start' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function graceEndDate()
    {
        return $this->grace_start ? Carbon::parse($this->grace_start)->addDays($this->grace_days) : null;
    }
}
