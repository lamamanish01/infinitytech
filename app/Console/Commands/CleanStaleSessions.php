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

            $cutoff = now()->subMinutes(5);

            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where(function ($q) use ($cutoff) {
                    $q->where('acctupdatetime', '<', $cutoff)
                    ->orWhereNull('acctupdatetime');
                })
                ->update([
                    'acctstoptime'        => now(),
                    'acctterminatecause'  => 'Stale-Session'
                ]);

            /*
            |--------------------------------------------------------------------------
            | SUCCESS LOG
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Stale sessions closed: {$updated}"
            ]);

            $this->info("Stale sessions closed: {$updated}");

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ERROR LOG
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
