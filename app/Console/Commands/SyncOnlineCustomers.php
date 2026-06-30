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

    // Make threshold configurable via config/cron.php or env
    private int $onlineThreshold = 15; // minutes
    private string $timezone = 'Asia/Kathmandu';

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
                'message' => "Online snapshot: {$count} users (TZ: {$this->timezone})",
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
     * Cutoff time using Asia/Kathmandu → converted to UTC for DB
     */
    private function getCutoffTime()
    {
        return now($this->timezone)
            ->subMinutes($this->onlineThreshold)
            ->utc();
    }

    /**
     * Fetch online users efficiently.
     * - Uses a union approach to avoid OR with null checks (better index usage).
     * - Still applies DISTINCT to get unique usernames.
     */
    private function getOnlineUsers($cutoff): array
    {
        // First subquery: sessions with acctupdatetime >= cutoff
        $sub1 = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereNotNull('acctupdatetime')
            ->where('acctupdatetime', '>=', $cutoff)
            ->select('username');

        // Second subquery: sessions with null acctupdatetime but acctstarttime >= cutoff
        $sub2 = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereNull('acctupdatetime')
            ->where('acctstarttime', '>=', $cutoff)
            ->select('username');

        // Union them, then get distinct usernames
        return $sub1->union($sub2)
            ->distinct()
            ->pluck('username')
            ->toArray();
    }

    /**
     * Cache online snapshot with a TTL long enough to serve between cron runs.
     * The TTL should be at least the cron interval; we set it to 5 minutes.
     */
    private function storeCache(array $users): void
    {
        // Use a TTL that is longer than the typical cron interval (e.g., 5 minutes)
        Cache::put(self::CACHE_KEY, $users, now()->addMinutes(5));
    }

    /**
     * Log changes only, with more detailed diff information.
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
                'added'    => array_slice($added, 0, 10),  // limit to avoid huge logs
                'removed'  => array_slice($removed, 0, 10),
                'timezone' => $this->timezone,
            ]);

            Cache::put(self::CACHE_KEY . '_previous_count', $count, now()->addDay());
            Cache::put(self::CACHE_KEY . '_previous_users', $currentUsers, now()->addDay());
        }
    }
}
