<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = ['customer_id', 'recharge_id', 'billing_date', 'internet_plan', 'amount'];
}
