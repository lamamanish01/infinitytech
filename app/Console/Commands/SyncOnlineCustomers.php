<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\CronLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOnlineCustomers extends Command
{
    protected $signature = 'customers:sync-online';

    protected $description = 'Sync online customers snapshot from radacct (no DB flag)';

    public function handle(): int
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            Log::info('Sync Online customer cron skipped (disabled)');
            return self::SUCCESS;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | STEP 1: GET ACTIVE SESSIONS
            |--------------------------------------------------------------------------
            */
            $sessions = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->get();

            $onlineUsers = [];

            /*
            |--------------------------------------------------------------------------
            | STEP 2: FILTER ONLINE USERS (15 MIN RULE)
            |--------------------------------------------------------------------------
            */
            foreach ($sessions as $session) {

                $last = $session->acctupdatetime ?? $session->acctstarttime;

                if (!$last) {
                    continue;
                }

                $last = Carbon::parse($last);

                if ($last->gt(now()->subMinutes(15))) {
                    $onlineUsers[] = $session->username;
                }
            }

            $onlineUsers = array_unique($onlineUsers);

            /*
            |--------------------------------------------------------------------------
            | STEP 3: OPTIONAL CACHE SNAPSHOT (NOT DB UPDATE)
            |--------------------------------------------------------------------------
            | You can store in cache for fast dashboard usage
            |--------------------------------------------------------------------------
            */
            cache()->put('online_customers', $onlineUsers, now()->addMinutes(2));

            /*
            |--------------------------------------------------------------------------
            | STEP 4: LOG
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => 'Online snapshot synced: ' . count($onlineUsers)
            ]);

            $this->info('✔ Online snapshot synced: ' . count($onlineUsers));

            return self::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
