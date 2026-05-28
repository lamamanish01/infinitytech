<?php

namespace App\Console\Commands;

use App\Models\CronLog;
use App\Models\Customer;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExpiredCustomers extends Command
{
    protected $signature = 'customers:update-expired';
    protected $description = 'Update customer status and disconnect expired users';

    public function handle()
    {
        Log::info('Customer expiry cron started');

        $radius = app(RadiusService::class);
        $mk     = app(MikrotikService::class);

        $stats = [
            'processed' => 0,
            'updated'   => 0,
            'expired'   => 0,
            'failed'    => 0,
        ];

        Customer::with(['mikrotik', 'internetPlan'])
            ->chunkById(200, function ($customers) use ($radius, $mk, &$stats) {

                foreach ($customers as $customer) {

                    try {

                        $stats['processed']++;

                        $newStatus = $customer->calculateStatus();
                        $oldStatus = $customer->status;

                        // ✔ SKIP if no change
                        if ($newStatus === $oldStatus) {
                            continue;
                        }

                        $customer->update([
                            'status' => $newStatus
                        ]);

                        $stats['updated']++;

                        // ✔ sync radius only when status changes
                        $radius->syncCustomer($customer);

                        // ✔ handle expiry actions
                        if (in_array($newStatus, ['expired', 'suspended', 'discontinued'])) {

                            $session = DB::table('radacct')
                                ->where('username', $customer->username)
                                ->whereNull('acctstoptime')
                                ->latest('radacctid')
                                ->first();

                            // ✔ disconnect only if session exists
                            if ($session && $customer->mikrotik) {
                                $mk->disconnectPPPoE(
                                    $customer->mikrotik,
                                    $customer->username
                                );
                            }

                            $stats['expired']++;
                        }

                    } catch (\Throwable $e) {

                        $stats['failed']++;

                        Log::error('Customer cron error', [
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
                "Expired: {$stats['expired']} | " .
                "Failed: {$stats['failed']}"
        ]);

        Log::info('Customer expiry cron completed', $stats);

        return self::SUCCESS;
    }
}
