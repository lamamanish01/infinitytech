<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['billing_id', 'invoice_date', 'status', 'amount'];
}
