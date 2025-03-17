<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'contact_number', 'amount', 'remarks'];


    public function users()
}
