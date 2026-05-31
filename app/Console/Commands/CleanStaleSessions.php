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
        | CRON CONTROL (ENABLE / DISABLE)
        |--------------------------------------------------------------------------
        */
        $job = CronJob::where('key', 'stale_sessions')->first();

        if (!$job || !$job->is_active) {
            $this->info('Stale session cron disabled');
            return self::SUCCESS;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | ISP SAFE CUTOFF (15 MINUTES)
            |--------------------------------------------------------------------------
            */
            $cutoff = Carbon::now()->subMinutes(15);

            /*
            |--------------------------------------------------------------------------
            | CLOSE STALE SESSIONS
            |--------------------------------------------------------------------------
            */
            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where(function ($q) use ($cutoff) {

                    $q->where('acctupdatetime', '<', $cutoff)
                      ->orWhere(function ($q2) use ($cutoff) {
                          $q2->whereNull('acctupdatetime')
                             ->where('acctstarttime', '<', $cutoff);
                      });

                })
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Stale-Session'
                ]);

            /*
            |--------------------------------------------------------------------------
            | SUCCESS LOG
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
            | ERROR LOG
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
