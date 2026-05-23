<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';
    protected $description = 'Clean stale FreeRADIUS sessions (radacct) after 5 minutes inactivity';

    public function handle()
    {
        try {

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

            /*
            |--------------------------------------------------------------------------
            | SUCCESS CRON LOG
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Cleaned {$updated} stale sessions"
            ]);

            $this->info("Cleaned {$updated} stale sessions.");

            return Command::SUCCESS;

        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | FAILED CRON LOG
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
