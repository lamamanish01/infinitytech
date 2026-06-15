<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronJob;
use App\Models\CronLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BindMac extends Command
{
    protected $signature = 'customers:bind-mac';
    protected $description = 'Auto bind MAC from active RADIUS sessions';

    public function handle(): int
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job configuration not found for: {$this->signature}");
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->error("Cron job '{$this->signature}' is inactive. Skipping execution.");
            return self::FAILURE;
        }

        $bound = 0;
        $skipped = 0;
        $updated = 0;

        try {
            Customer::chunkById(200, function ($customers) use (&$bound, &$skipped, &$updated) {
                foreach ($customers as $customer) {
                    try {
                        if (empty($customer->username)) {
                            $skipped++;
                            continue;
                        }

                        // Get latest active RADIUS session
                        $session = DB::table('radacct')
                            ->where('username', $customer->username)
                            ->whereNull('acctstoptime')
                            ->orderByDesc('radacctid')
                            ->first();

                        if (!$session) {
                            $skipped++;
                            continue;
                        }

                        $rawMac = $session->callingstationid ?? null;
                        if (!$rawMac) {
                            $skipped++;
                            continue;
                        }

                        $mac = $this->normalizeMac($rawMac);
                        if (!$mac || !$this->isValidMac($mac)) {
                            $skipped++;
                            continue;
                        }

                        // Prevent duplicate MAC across different customers
                        $exists = Customer::where('mac_address', $mac)
                            ->where('id', '!=', $customer->id)
                            ->exists();

                        if ($exists) {
                            $skipped++;
                            continue;
                        }

                        // Update only if changed
                        if ($customer->mac_address !== $mac) {
                            $customer->update(['mac_address' => $mac]);
                            $updated++;
                        } else {
                            $bound++; // already correct
                        }
                    } catch (\Throwable $e) {
                        Log::error('MAC bind error', [
                            'customer_id' => $customer->id ?? null,
                            'error'       => $e->getMessage(),
                        ]);
                    }
                }
            });

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Updated: {$updated} | Already OK: {$bound} | Skipped: {$skipped}",
            ]);

            $this->info("✔ MAC Bind Completed");
            $this->info("Updated: {$updated}, OK: {$bound}, Skipped: {$skipped}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Normalize MAC address to format XX:XX:XX:XX:XX:XX
     */
    private function normalizeMac(?string $mac): ?string
    {
        if (!$mac) return null;
        $mac = strtoupper(trim($mac));
        $mac = preg_replace('/[^A-F0-9]/', '', $mac);
        if (strlen($mac) !== 12) return null;
        return implode(':', str_split($mac, 2));
    }

    /**
     * Validate MAC address format
     */
    private function isValidMac(string $mac): bool
    {
        return (bool) preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/', $mac);
    }
}
