<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';
    protected $description = 'Clean stale FreeRADIUS sessions (radacct) after 5 minutes inactivity';

    public function handle()
    {
        $updated = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->where(function ($q) {
                $q->where('acctupdatetime', '<', now()->subMinutes(5))
                ->orWhereNull('acctupdatetime');
            })
            ->update([
                'acctstoptime' => now(),
                'acctterminatecause' => 'Stale-Session'
            ]);

        $this->info("Cleaned {$updated} stale sessions.");
    }
}
