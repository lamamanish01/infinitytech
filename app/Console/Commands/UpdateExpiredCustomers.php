<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronJob;
use App\Models\CronLog;
use App\Models\GracePeriod;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateExpiredCustomers extends Command
{
    protected $signature = 'customers:update-expired';
    protected $description = 'Handle ISP lifecycle using grace table';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            Log::info('Expire customer cron skipped (disabled)');
            return self::SUCCESS;
        }

        Log::info('ISP cron started');

        $radius = app(RadiusService::class);
        $mk     = app(MikrotikService::class);

        $stats = [
            'processed' => 0,
            'updated'   => 0,
            'grace'     => 0,
            'expired'   => 0,
            'failed'    => 0,
        ];

        try {

            Customer::with(['mikrotik', 'internetPlan'])
                ->chunkById(200, function ($customers) use ($radius, $mk, &$stats) {

                    foreach ($customers as $customer) {

                        try {

                            $stats['processed']++;

                            if (!$customer->expire_date) {
                                continue;
                            }

                            $now = now();
                            $expire = Carbon::parse($customer->expire_date)->endOfDay();

                            $grace = GracePeriod::where('customer_id', $customer->id)
                                ->orderByDesc('grace_end')
                                ->first();

                            $graceEnd = $grace && $grace->grace_end
                                ? Carbon::parse($grace->grace_end)
                                : $expire;

                            if ($now->greaterThan($graceEnd)) {
                                $newStatus = 'expired';
                            } elseif ($now->greaterThan($expire)) {
                                $newStatus = 'grace';
                            } else {
                                $newStatus = 'active';
                            }

                            if ($newStatus === 'expired') {
                                $stats['expired']++;
                            } elseif ($newStatus === 'grace') {
                                $stats['grace']++;
                            }

                            $customer->update([
                                'status' => $newStatus
                            ]);

                            $stats['updated']++;

                            try {
                                $radius->syncCustomer($customer);
                            } catch (\Throwable $e) {
                                Log::error('Radius sync failed', [
                                    'customer_id' => $customer->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                            /**
                             * =========================
                             * MICROTIK DISCONNECT FIXED
                             * =========================
                             */
                            if ($newStatus === 'expired' && !empty($customer->username)) {

                                try {

                                    Log::info('Disconnecting user from MikroTik', [
                                        'username' => $customer->username,
                                        'customer_id' => $customer->id
                                    ]);

                                    $result = $mk->disconnectPPPoE($customer->username);

                                    Log::info('MikroTik disconnect result', [
                                        'username' => $customer->username,
                                        'result' => $result
                                    ]);

                                } catch (\Throwable $e) {

                                    Log::error('MikroTik disconnect failed', [
                                        'customer_id' => $customer->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }

                                DB::table('radacct')
                                    ->where('username', $customer->username)
                                    ->whereNull('acctstoptime')
                                    ->update([
                                        'acctstoptime' => now(),
                                        'acctterminatecause' => 'Expired'
                                    ]);

                                try {
                                    $radius->removeCustomer($customer);
                                } catch (\Throwable $e) {
                                    Log::error('Radius remove failed', [
                                        'customer_id' => $customer->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }

                        } catch (\Throwable $e) {

                            $stats['failed']++;

                            Log::error('Customer lifecycle error', [
                                'customer_id' => $customer->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' =>
                    "Processed: {$stats['processed']} | " .
                    "Updated: {$stats['updated']} | " .
                    "Grace: {$stats['grace']} | " .
                    "Expired: {$stats['expired']} | " .
                    "Failed: {$stats['failed']}"
            ]);

            Log::info('ISP cron finished', $stats);

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            Log::error('ISP cron crashed', [
                'error' => $e->getMessage()
            ]);
        }

        return self::SUCCESS;
    }
}
