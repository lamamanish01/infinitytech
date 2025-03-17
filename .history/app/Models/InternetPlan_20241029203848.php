<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetPlan extends Model
{
    public function plan_type()
    {
        return $this->HasMany(InternetPlanType::class, 'name');
    }
}
