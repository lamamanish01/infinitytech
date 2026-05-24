<?php

namespace App\Models;

use App\Models\BranchTransaction;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Branch extends Model
{
    protected $fillable = ['name', 'address', 'contact_number', 'balance', 'remarks'];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    public function users()
    {
        $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(BranchTransaction::class);
    }

    public function credit($amount)
    {
        $this->increment('balance', $amount);
    }

    public function debit($amount)
    {
        $this->decrement('balance', $amount);
    }

    public function decrementBalance($amount)
    {
        $this->decrement('balance', $amount);
    }

    public function addBalance($amount, $remarks = null)
    {
        return DB::transaction(function () use ($amount, $remarks) {

            // 1. update branch balance
            $this->increment('balance', $amount);

            // 2. create ledger entry
            return BranchTransaction::create([
                'branch_id' => $this->id,
                'type' => 'credit',
                'amount' => $amount,
                'source' => 'admin',
                'remarks' => $remarks ?? 'Balance added by admin'
            ]);
        });
    }
}
