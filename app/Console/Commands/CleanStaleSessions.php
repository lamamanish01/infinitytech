<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use Carbon\Carbon;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';

    protected $description = 'Close stale FreeRADIUS sessions properly';

    public function handle(): int
    {
        try {

            $cutoff = Carbon::now()->subMinutes(10);

            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')

                ->where(function ($q) use ($cutoff) {
                    $q->where('acctstarttime', '<', $cutoff)
                      ->orWhereNull('acctstarttime');
                })

                ->update([
                    'acctstoptime' => Carbon::now(),
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
