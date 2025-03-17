<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class GracePeriod extends Model
{
    protected $fillable = ['customer_id', 'grace_days'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deductGraceDays()
    {
        if ($this->grace_days > 0)
        {
            $this->grace_days -=1;

            if ($this->grace_days == 0)
            {
                $this->delete();
            } else {
                $this->save();
            }
        }
    }
}
