<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CronJob;
use App\Models\CronLog;

class SyncOnlineCustomers extends Command
{
    protected $signature = 'customers:sync-online';
    protected $description = 'Sync online customers snapshot from radacct';

    private const CACHE_KEY = 'online_customers';

    /**
     * Users active within this many minutes are considered online.
     */
    private int $onlineThreshold = 15;

    public function handle(): int
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job not found: {$this->signature}");
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->warn("Cron job inactive: {$this->signature}");
            return self::FAILURE;
        }

        try {
            $cutoff = $this->getCutoffTime();
            $onlineUsers = $this->getOnlineUsers($cutoff);
            $count = count($onlineUsers);

            $this->storeCache($onlineUsers);
            $this->logIfChanged($count);

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Online snapshot: {$count} users",
            ]);

            $this->info("✔ Online snapshot synced: {$count} users");

            return self::SUCCESS;

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Cutoff time in the same timezone as the database.
     * (Assumes app timezone === DB timezone, both UTC ideally.)
     */
    private function getCutoffTime()
    {
        return now()->subMinutes($this->onlineThreshold);
    }

    /**
     * Fetch online users with a UNION query for performance.
     *
     * Recommended indexes:
     *   ALTER TABLE radacct ADD INDEX idx_active_updatetime (acctstoptime, acctupdatetime);
     *   ALTER TABLE radacct ADD INDEX idx_active_starttime (acctstoptime, acctstarttime);
     */
    private function getOnlineUsers($cutoff): array
    {
        $sub1 = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereNotNull('acctupdatetime')
            ->where('acctupdatetime', '>=', $cutoff)
            ->select('username');

        $sub2 = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereNull('acctupdatetime')
            ->where('acctstarttime', '>=', $cutoff)
            ->select('username');

        return $sub1->union($sub2)
            ->distinct()
            ->pluck('username')
            ->toArray();
    }

    /**
     * Cache the snapshot with a TTL longer than the cron interval.
     */
    private function storeCache(array $users): void
    {
        Cache::put(self::CACHE_KEY, $users, now()->addMinutes(5));
    }

    /**
     * Log changes only when the count differs.
     */
    private function logIfChanged(int $count): void
    {
        $previous = Cache::get(self::CACHE_KEY . '_previous_count');

        if ($previous !== $count) {
            $currentUsers = Cache::get(self::CACHE_KEY, []);
            $prevUsers = Cache::get(self::CACHE_KEY . '_previous_users', []);

            $added = array_diff($currentUsers, $prevUsers);
            $removed = array_diff($prevUsers, $currentUsers);

            Log::info('Online customers changed', [
                'previous' => $previous,
                'current'  => $count,
                'added'    => array_slice($added, 0, 10),
                'removed'  => array_slice($removed, 0, 10),
            ]);

            Cache::put(self::CACHE_KEY . '_previous_count', $count, now()->addDay());
            Cache::put(self::CACHE_KEY . '_previous_users', $currentUsers, now()->addDay());
        }
    }
}
