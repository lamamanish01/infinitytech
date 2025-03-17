<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'address', 'email', 'username', 'password', 'contact_number', 'internetplan_id', 'branch_id', 'registered'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'name');
    }

    public function internetplan()
    {
        return $this->belong(InternetPlan::class, 'name');
    }
}

