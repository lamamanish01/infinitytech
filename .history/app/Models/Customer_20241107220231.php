<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'address', 'email', 'username', 'password', 'contact_number', 'internetplan_id', 'branch_id', 'registered', 'user_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'name');
    }

    public function internetplan()
    {
        return $this->belongsTo(InternetPlan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}

