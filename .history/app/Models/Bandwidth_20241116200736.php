<?php

namespace App\Models;

use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Bandwidth extends Model
{
    protected $fillable = ['name', 'upload_rate', 'download_rate'];

    public function internetplan()
    {
        return $this->hasMan(InternetPlan::class);
    }
}
