<?php

namespace App\Models;

use App\Models\Bandwidth;
use Illuminate\Database\Eloquent\Model;

class InternetPlan extends Model
{
    protected $fillable = ['name', 'bandwidth_name', 'price', 'duration', 'type'];

    public function bandwidth()
    {
        return $this->hasMany(Bandwidth::class);
    }

    public function recharges()
    {
        return $this->hasMany()
    }
}
