<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class GracePeriod extends Model
{
    protected $fillable = ['customer_id', 'grace_days', 'grace_start'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
