<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternetPlan extends Model
{
    protected $fillable = ['name', 'bandwidth', 'price', 'duration'];
}
