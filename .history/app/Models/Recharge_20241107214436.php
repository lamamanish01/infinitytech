<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{

    public function internetplan()
    {
        return $this->hasMany(InternetPlan::class, 'id');
    }
}
