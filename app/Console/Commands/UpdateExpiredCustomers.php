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
        /*
        |--------------------------------------------------------------------------
        | CRON CONTROL (ENABLE / DISABLE)
        |--------------------------------------------------------------------------
        */
        $job = CronJob::where('key', 'update_expire_customers')->first();

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

                            /*
                            |--------------------------------------------------------------------------
                            | VALIDATION
                            |--------------------------------------------------------------------------
                            */
                            if (!$customer->expire_date) {
                                continue;
                            }

                            $now = now();

                            $expire = Carbon::parse($customer->expire_date)->endOfDay();

                            /*
                            |--------------------------------------------------------------------------
                            | GRACE LOGIC (FIXED)
                            |--------------------------------------------------------------------------
                            */
                            $grace = GracePeriod::where('customer_id', $customer->id)
                                ->latest()
                                ->first();

                            $graceEnd = $expire;

                            if ($grace && $grace->grace_end) {
                                $graceEnd = Carbon::parse($grace->grace_end);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | STATUS CALCULATION
                            |--------------------------------------------------------------------------
                            */
                            if ($now->greaterThan($graceEnd)) {
                                $newStatus = 'expired';
                            } elseif ($now->greaterThan($expire)) {
                                $newStatus = 'grace';
                            } else {
                                $newStatus = 'active';
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | SKIP IF NO CHANGE
                            |--------------------------------------------------------------------------
                            */
                            if ($customer->status === $newStatus) {
                                continue;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | UPDATE CUSTOMER STATUS
                            |--------------------------------------------------------------------------
                            */
                            $customer->update([
                                'status' => $newStatus
                            ]);

                            $customer->refresh();

                            $stats['updated']++;

                            /*
                            |--------------------------------------------------------------------------
                            | RADIUS SYNC (SAFE)
                            |--------------------------------------------------------------------------
                            */
                            try {
                                $radius->syncCustomer($customer);
                            } catch (\Throwable $e) {
                                Log::error('Radius sync failed', [
                                    'customer_id' => $customer->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | EXPIRED HANDLING
                            |--------------------------------------------------------------------------
                            */
                            if ($newStatus === 'expired') {

                                $stats['expired']++;

                                try {
                                    $radius->removeCustomer($customer);
                                } catch (\Throwable $e) {
                                    Log::error('Radius remove failed', [
                                        'customer_id' => $customer->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }

                                /*
                                |-----------------------------
                                | CLOSE RADACCT SESSION (SAFE)
                                |-----------------------------
                                */
                                DB::table('radacct')
                                    ->where('username', $customer->username)
                                    ->whereNull('acctstoptime')
                                    ->whereNull('acctterminatecause')
                                    ->update([
                                        'acctstoptime' => now(),
                                        'acctterminatecause' => 'Expired'
                                    ]);

                                /*
                                |-----------------------------
                                | FORCE DISCONNECT
                                |-----------------------------
                                */
                                if ($customer->mikrotik) {
                                    try {
                                        $mk->disconnectPPPoE(
                                            $customer->mikrotik,
                                            $customer->username
                                        );
                                    } catch (\Throwable $e) {
                                        Log::error('Mikrotik disconnect failed', [
                                            'customer_id' => $customer->id,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                }
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | GRACE COUNT
                            |--------------------------------------------------------------------------
                            */
                            if ($newStatus === 'grace') {
                                $stats['grace']++;
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

            /*
            |--------------------------------------------------------------------------
            | FINAL LOG
            |--------------------------------------------------------------------------
            */
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
