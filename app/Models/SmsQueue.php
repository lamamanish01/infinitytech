<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsQueue extends Model
{
    protected $fillable = ['username', 'mobile', 'message', 'type', 'status', 'retry_count', 'send_at'];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT    = 'sent';
    const STATUS_FAILED  = 'failed';

    public function markAsSent()
    {
        $this->update(['status' => self::STATUS_SENT]);
    }

    public function markAsFailed()
    {
        $this->increment('retry_count');
        if ($this->retry_count >= 3) {
            $this->update(['status' => self::STATUS_FAILED]);
        }
    }
}
