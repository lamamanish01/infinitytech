<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetPlan extends Model
{
    protected $fillable = ['name', 'bandwidth_name', 'price', 'duration', 'type'];

    

    public function internetplantype()
    {
        return $this->belongsTo(InternetPlanType::class, 'id');
    }
}
