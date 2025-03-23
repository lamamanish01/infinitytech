<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{
    protected $primaryKey = 'radacctid';

    protected $table = "radacct";
    protected $guarded = ['id'];
    public $timestamps = false;

    public function Customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
