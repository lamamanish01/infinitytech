<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use Carbon\Carbon;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';

    protected $description = 'Safely close stale FreeRADIUS sessions';

    public function handle(): int
    {
        try {

            // ⚡ REAL ISP cutoff (safe window)
            $cutoff = Carbon::now()->subMinutes(15);

            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where(function ($q) use ($cutoff) {

                    // ✔ Case 1: No activity for long time
                    $q->where('acctupdatetime', '<', $cutoff)

                      // ✔ Case 2: broken sessions (no updates at all)
                      ->orWhere(function ($q2) use ($cutoff) {
                          $q2->whereNull('acctupdatetime')
                             ->where('acctstarttime', '<', $cutoff);
                      });
                })
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Stale-Session'
                ]);

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Stale sessions closed: {$updated}",
            ]);

            $this->info("Stale sessions closed: {$updated}");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
