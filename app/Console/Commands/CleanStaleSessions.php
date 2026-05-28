<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use Carbon\Carbon;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';

    protected $description = 'Close FreeRADIUS stale sessions after 5 minutes of inactivity';

    public function handle(): int
    {
        try {
            $cutoff = Carbon::now()->subMinutes(5);

            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where(function ($query) use ($cutoff) {
                    $query->where('acctupdatetime', '<', $cutoff)
                          ->orWhereNull('acctupdatetime');
                })
                ->update([
                    'acctstoptime'       => Carbon::now(),
                    'acctterminatecause' => 'Idle-Timeout',
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
