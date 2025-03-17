<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GracePeriod extends Model
{
    protected $fillable = ['customer_id', 'grace_days'];

    public functio customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
