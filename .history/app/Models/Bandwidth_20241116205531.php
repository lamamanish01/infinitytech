<?php

namespace App\Models;

use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Bandwidth extends Model
{
    protected $fillable = ['name', 'upload_rate', 'download_rate'];

    public function internetPlan()
    {
        return $this->hasMany(InternetPlan::class, 'bandwidth_id');
    }
}
