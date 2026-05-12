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
        $limit = now()->subMinutes(5);

        $updated = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->where('acctstarttime', '<', $limit)
            ->update([
                'acctstoptime' => now(),
                'acctterminatecause' => 'Stale-Session-5min-Cron'
            ]);

        $this->info("Stale sessions cleaned: {$updated}");

        return self::SUCCESS;
    }
}
