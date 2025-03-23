<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadCheck extends Model
{
    protected $table = "radcheck";
    protected $guarded = [];
    public $timestamps = false;

    public function customer()
    {
        return $this->belongTo(Customer::class, 'username', 'username');
    }
}

