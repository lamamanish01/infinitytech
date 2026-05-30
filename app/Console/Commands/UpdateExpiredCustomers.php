<?php

namespace App\Console\Commands;

use App\Models\CronLog;
use App\Models\Customer;
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

        Customer::with(['mikrotik', 'internetPlan'])
            ->chunkById(200, function ($customers) use ($radius, $mk, &$stats) {

                foreach ($customers as $customer) {

                    try {

                        $stats['processed']++;

                        /*
                        |--------------------------------------------------------------------------
                        | LOAD DATES
                        |--------------------------------------------------------------------------
                        */
                        if (!$customer->expire_date) {
                            continue;
                        }

                        $now = now();

                        $expire = Carbon::parse($customer->expire_date)->endOfDay();

                        $grace = GracePeriod::where('customer_id', $customer->id)
                            ->latest()
                            ->first();

                        $graceEnd = $grace && $grace->grace_end
                            ? Carbon::parse($grace->grace_end)
                            : $expire;

                        /*
                        |--------------------------------------------------------------------------
                        | DETERMINE STATUS (INLINE ISP LOGIC)
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
                        | UPDATE STATUS
                        |--------------------------------------------------------------------------
                        */
                        $customer->update([
                            'status' => $newStatus
                        ]);

                        $customer->refresh();

                        $stats['updated']++;

                        /*
                        |--------------------------------------------------------------------------
                        | SYNC RADIUS
                        |--------------------------------------------------------------------------
                        */
                        $radius->syncCustomer($customer);

                        /*
                        |--------------------------------------------------------------------------
                        | EXPIRED HANDLING (FINAL CUT)
                        |--------------------------------------------------------------------------
                        */
                        if ($newStatus === 'expired') {

                            $stats['expired']++;

                            $radius->removeCustomer($customer);

                            // CLOSE SESSION
                            DB::table('radacct')
                                ->where('username', $customer->username)
                                ->whereNull('acctstoptime')
                                ->update([
                                    'acctstoptime' => now(),
                                    'acctterminatecause' => 'Expired'
                                ]);

                            // FORCE DISCONNECT
                            if ($customer->mikrotik) {
                                $mk->disconnectPPPoE(
                                    $customer->mikrotik,
                                    $customer->username
                                );
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | GRACE (NO DISCONNECT)
                        |--------------------------------------------------------------------------
                        */
                        if ($newStatus === 'grace') {
                            $stats['grace']++;
                        }

                    } catch (\Throwable $e) {

                        $stats['failed']++;

                        Log::error('ISP cron error', [
                            'customer_id' => $customer->id,
                            'username'    => $customer->username,
                            'error'       => $e->getMessage(),
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

        return self::SUCCESS;
    }
}
