<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'check_type',
        'snmp_community',
        'snmp_version',
        'snmp_port',
        'snmp_timeout',
        'snmp_oid',
        'status',
        'uptime',
        'response_time',
        'success_count',
        'total_count',
        'last_checked_at',
    ];

    protected $casts = [
        'uptime'          => 'float',
        'response_time'   => 'integer',
        'success_count'   => 'integer',
        'total_count'     => 'integer',
        'last_checked_at' => 'datetime',
        'snmp_port'       => 'integer',
        'snmp_timeout'    => 'integer',
    ];

    // ========== CONSTANTS ==========
    public const STATUS_UP      = 'up';
    public const STATUS_DOWN    = 'down';
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNKNOWN = 'unknown';

    public const CHECK_PING = 'ping';
    public const CHECK_SNMP = 'snmp';

    public static function getStatuses(): array
    {
        return [self::STATUS_UP, self::STATUS_DOWN, self::STATUS_PENDING, self::STATUS_UNKNOWN];
    }

    public static function getCheckTypes(): array
    {
        return [self::CHECK_PING, self::CHECK_SNMP];
    }

    // ========== ACCESSORS ==========
    public function getFormattedUptimeAttribute(): string
    {
        return $this->uptime !== null ? number_format($this->uptime, 2) . '%' : 'N/A';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UP      => '🟢 Up',
            self::STATUS_DOWN    => '🔴 Down',
            self::STATUS_PENDING => '🟡 Pending',
            default              => '⚪ Unknown',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UP      => 'badge-success',
            self::STATUS_DOWN    => 'badge-danger',
            self::STATUS_PENDING => 'badge-warning',
            default              => 'badge-secondary',
        };
    }

    // ========== HELPERS ==========
    public function isUp(): bool   { return $this->status === self::STATUS_UP; }
    public function isDown(): bool { return $this->status === self::STATUS_DOWN; }

    // ========== SCOPES ==========
    public function scopeUp($query)   { return $query->where('status', self::STATUS_UP); }
    public function scopeDown($query) { return $query->where('status', self::STATUS_DOWN); }
}
