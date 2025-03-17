<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'address', 'email', 'username', 'password', 'contact_number', 'branch_id', 'registered'];

    public function branch()
    {
        return $This->belongTo(Branch::class, '')
    }
}
