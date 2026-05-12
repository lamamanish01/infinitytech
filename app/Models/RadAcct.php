<?php

namespace App\Models;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{

    protected $primaryKey = 'radacctid';
    protected $table = "radacct";
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $appends = [
        'ip_address',
        'start_time',
        'ppp_server',
        'nas_ip',
        'mac_address',
        'upload_mb',
        'download_mb',
        'session_time_human',
    ];

    public function Customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getIpAddressAttribute()
    {
        return $this->framedipaddress;
    }

    public function getStartTimeAttribute()
    {
        return $this->acctstarttime;
    }

    public function getPppServerAttribute()
    {
        return $this->calledstationid;
    }

    public function getNasIpAttribute()
    {
        return $this->nasipaddress;
    }

    public function getMacAddressAttribute()
    {
        return $this->callingstationid;
    }

    public function getUploadMbAttribute()
    {
    $bytes = $this->acctinputoctets;

    // Convert to GB if greater than or equal to 1 GB
    if ($bytes >= 1024 * 1024 * 1024) {

        return round(
            $bytes / 1024 / 1024 / 1024,
            2
        ) . ' GB';
    }

    // Otherwise show MB
    return round(
        $bytes / 1024 / 1024,
        2
    ) . ' MB';
    }

    public function getDownloadMbAttribute()
    {
    $bytes = $this->acctoutputoctets;

    // Convert to GB if usage is 1 GB or more
    if ($bytes >= 1024 * 1024 * 1024) {

        return round(
            $bytes / 1024 / 1024 / 1024,
            2
        ) . ' GB';
    }

    // Otherwise show MB
    return round(
        $bytes / 1024 / 1024,
        2
    ) . ' MB';
    }

    public function getSessionTimeHumanAttribute()
    {
        $seconds = Carbon::parse($this->acctstarttime)
            ->diffInSeconds(now());

        return gmdate('H:i:s', $seconds);
    }
}
