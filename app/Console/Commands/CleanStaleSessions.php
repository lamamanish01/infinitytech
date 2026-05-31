<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use App\Models\CronJob;
use Carbon\Carbon;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';
    protected $description = 'Safely close stale FreeRADIUS sessions';

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | CRON CONTROL
        |--------------------------------------------------------------------------
        */
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            $this->info('Stale session cron disabled');
            return self::SUCCESS;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | SAFE CUTOFF (15 MINUTES)
            |--------------------------------------------------------------------------
            */
            $cutoff = Carbon::now()->subMinutes(15);

            /*
            |--------------------------------------------------------------------------
            | CLOSE STALE SESSIONS (FIXED QUERY)
            |--------------------------------------------------------------------------
            */
            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where('acctstarttime', '<', $cutoff)
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Stale-Session'
                ]);

            /*
            |--------------------------------------------------------------------------
            | LOG SUCCESS
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Stale sessions closed: {$updated}",
            ]);

            $this->info("✔ Stale sessions closed: {$updated}");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG ERROR
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
