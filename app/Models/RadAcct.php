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

    protected $casts = [
        'acctstarttime' => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime' => 'datetime',
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

    if ($bytes >= 1024 * 1024 * 1024) {

        return round(
            $bytes / 1024 / 1024 / 1024,
            2
        ) . ' GB';
    }

    return round(
        $bytes / 1024 / 1024,
        2
    ) . ' MB';
    }

    public function getDownloadMbAttribute()
    {
    $bytes = $this->acctoutputoctets;

    if ($bytes >= 1024 * 1024 * 1024) {

        return round(
            $bytes / 1024 / 1024 / 1024,
            2
        ) . ' GB';
    }

    return round(
        $bytes / 1024 / 1024,
        2
    ) . ' MB';
    }


    public function getSessionTimeHumanAttribute()
    {
        if (!$this->acctstarttime) {
            return '-';
        }

        $start = Carbon::parse($this->acctstarttime);

        $end = $this->acctstoptime
            ? Carbon::parse($this->acctstoptime)
            : now();

        $seconds = $start->diffInSeconds($end);

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return $days
            ? sprintf('%dd %02d:%02d:%02d', $days, $hours, $minutes, $secs)
            : sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
