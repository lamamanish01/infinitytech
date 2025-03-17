<?php

namespace App\Models;

use App\Models\Recharge;
use App\Models\Bandwidth;
use Illuminate\Database\Eloquent\Model;

class InternetPlan extends Model
{
    protected $fillable = ['name', 'bandwidth_name', 'price', 'duration', 'type', 'rate_limit'];

    public function recharges()
    {
        return $this->hasMany(Recharge::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'internetplan', 'name');
    }
}
