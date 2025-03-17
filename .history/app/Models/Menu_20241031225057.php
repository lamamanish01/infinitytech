<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['title', 'url', 'icon', 'parent_id', 'order', 'role'];

    public function children()
    {
        return $this->hasMany($this, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo($this, 'parent_id');
    }
}


