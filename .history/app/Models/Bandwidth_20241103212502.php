<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bandwidth extends Model
{
    protected $fillable = ['name', 'upload_rate', 'download_rate'];

    public function internetplan()
    {
        return $this->belongsTo(InternetPlan::class);
    }
}
