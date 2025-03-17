<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected fillable = ['name', 'address', 'email', 'username', 'password', 'contact_number']
}
