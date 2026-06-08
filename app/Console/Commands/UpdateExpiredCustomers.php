<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronJob;
use App\Models\CronLog;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateExpiredCustomers extends Command
{
    protected $signature = 'customers:update-expired';
    protected $description = 'Handle ISP customer lifecycle (active, grace, expired)';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            Log::info('Customer expiry cron skipped (disabled)');
            return self::SUCCESS;
        }

        Log::info('Customer expiry cron started');

        $mikrotik = app(MikrotikService::class);
        $radius   = app(RadiusService::class);

        $stats = [
            'processed' => 0,
            'updated'   => 0,
            'grace'     => 0,
            'expired'   => 0,
            'failed'    => 0,
        ];

        try {
            // Eager load active grace period (only current one)
            Customer::with(['activeGracePeriod' => function ($query) {
                $query->where('grace_start', '<=', now())
                      ->where('grace_end', '>=', now())
                      ->latest('grace_end');
            }])->chunkById(200, function ($customers) use ($mikrotik, $radius, &$stats) {

                foreach ($customers as $customer) {
                    try {
                        $stats['processed']++;

                        if (!$customer->expire_date) {
                            continue;
                        }

                        $now = now();
                        $expireDate = $customer->expire_date->copy()->endOfDay();

                        // Get active grace period from eager loaded relation
                        $activeGrace = $customer->activeGracePeriod->first();
                        $cutoffDate = $activeGrace ? Carbon::parse($activeGrace->grace_end) : $expireDate;

                        if ($now->greaterThan($cutoffDate)) {
                            $newStatus = 'expired';
                        } elseif ($now->greaterThan($expireDate)) {
                            $newStatus = 'grace';
                        } else {
                            $newStatus = 'active';
                        }

                        $oldStatus = $customer->status;

                        // No change
                        if ($oldStatus === $newStatus) {
                            if ($newStatus === 'grace') {
                                $stats['grace']++;
                            }
                            continue;
                        }

                        // --- Status change ---
                        DB::transaction(function () use ($customer, $newStatus, $oldStatus, $mikrotik, $radius, &$stats) {
                            $customer->update(['status' => $newStatus]);
                            $stats['updated']++;

                            if ($newStatus === 'grace') {
                                $stats['grace']++;
                            }

                            // One‑time cleanup when becoming expired
                            if ($newStatus === 'expired') {
                                $stats['expired']++;

                                if (!empty($customer->username)) {
                                    // Disconnect from MikroTik
                                    try {
                                        Log::info('Disconnecting expired user', [
                                            'username' => $customer->username,
                                            'customer_id' => $customer->id
                                        ]);
                                        $mikrotik->disconnectPPPoE($customer->username);
                                    } catch (\Throwable $e) {
                                        Log::error('MikroTik disconnect failed', [
                                            'customer_id' => $customer->id,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }

                                    // Close RADIUS accounting session
                                    DB::table('radacct')
                                        ->where('username', $customer->username)
                                        ->whereNull('acctstoptime')
                                        ->update([
                                            'acctstoptime' => now(),
                                            'acctterminatecause' => 'Expired'
                                        ]);

                                    // Remove from RADIUS
                                    try {
                                        $radius->removeCustomer($customer);
                                    } catch (\Throwable $e) {
                                        Log::error('Radius remove failed', [
                                            'customer_id' => $customer->id,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                }
                            }
                        });

                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        Log::error('Customer processing failed', [
                            'customer_id' => $customer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Processed: {$stats['processed']} | " .
                             "Updated: {$stats['updated']} | " .
                             "Grace: {$stats['grace']} | " .
                             "Expired: {$stats['expired']} | " .
                             "Failed: {$stats['failed']}"
            ]);

            Log::info('Customer expiry cron finished', $stats);

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            Log::error('Customer expiry cron crashed', ['error' => $e->getMessage()]);
        }

        return self::SUCCESS;
    }
}
