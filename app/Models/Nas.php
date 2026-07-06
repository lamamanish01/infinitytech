<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nas extends Model
{
    use SoftDeletes;
    protected $table = "nas";
    protected $guarded = [];
    public $timestamps = false;
}
