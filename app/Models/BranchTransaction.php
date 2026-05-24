<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchTransaction extends Model
{
    protected $fillable = [
        'branch_id',
        'type',
        'amount',
        'source',
        'customer_id',
        'reversal_of',
        'is_void',
        'remarks'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reverse()
    {
        // 1. mark original as void
        $this->is_void = true;
        $this->save();

        // 2. create reverse entry
        self::create([
            'branch_id' => $this->branch_id,
            'type' => $this->type == 'credit' ? 'debit' : 'credit',
            'amount' => $this->amount,
            'source' => 'reverse',
            'reversed_from' => $this->id,
        ]);

        // 3. recalculate and update branch balance
        $balance = self::where('branch_id', $this->branch_id)
            ->where('is_void', false)
            ->selectRaw("
                SUM(CASE WHEN type='credit' THEN amount ELSE 0 END)
                -
                SUM(CASE WHEN type='debit' THEN amount ELSE 0 END)
                as balance
            ")
            ->value('balance') ?? 0;

        Branch::where('id', $this->branch_id)
            ->update(['balance' => $balance]);
    }
}
