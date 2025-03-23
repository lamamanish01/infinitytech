<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'location', 'contact_number', 'amount', 'remarks'];


    public function users()
    {
        $this->hasMany(User::class);
    }
}
