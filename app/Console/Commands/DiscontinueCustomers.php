<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronJob;
use App\Models\CronLog;
use App\Services\RadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DiscontinueCustomers extends Command
{
    protected $signature = 'customers:discontinue';
    protected $description = 'Discontinue customers with no recharge in 3 months (via RADIUS)';

    public function handle(RadiusService $radiusService): int
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

        // --- Stats counters ---
        $discontinued = 0;   // successfully disabled in both RADIUS & local DB
        $failed = 0;         // RADIUS call failed (local DB NOT updated)
        $skipped = 0;        // already discontinued or not eligible

        $cutoff = Carbon::now()->subMonths(3);

        try {
            // Process customers in chunks to avoid memory overload
            Customer::where('status', '!=', 'discontinued')
                    ->where('expire_date', '<', $cutoff)
                    ->chunkById(200, function ($customers) use ($radiusService, &$discontinued, &$failed, &$skipped) {
                        foreach ($customers as $customer) {
                            try {
                                // 1) Call RADIUS service to discontinue the customer
                                $radiusSuccess = $radiusService->discontinueCustomer($customer);

                                // 2) If successful, update local status; otherwise increment fail counter
                                if ($radiusSuccess) {
                                    $customer->status = 'discontinued';
                                    $customer->save();
                                    $discontinued++;
                                    $this->info("✓ Customer #{$customer->id} discontinued.");
                                } else {
                                    $failed++;
                                    $this->warn("✗ RADIUS failed for customer #{$customer->id}.");
                                    // Optionally log the failure for manual intervention
                                    Log::warning('RADIUS discontinuation failed', [
                                        'customer_id' => $customer->id,
                                        'username'    => $customer->username,
                                    ]);
                                }

                            } catch (\Throwable $e) {
                                $failed++;
                                Log::error('Error processing customer for discontinuation', [
                                    'customer_id' => $customer->id ?? null,
                                    'error'       => $e->getMessage(),
                                ]);
                            }
                        }
                    });

            // --- Log success in CronLog ---
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Discontinued: {$discontinued} | Failed: {$failed} | Skipped: {$skipped}",
            ]);

            $this->info("✔ Discontinuation completed.");
            $this->info("Discontinued: {$discontinued}, Failed: {$failed}, Skipped: {$skipped}");

            return self::SUCCESS;

        } catch (\Throwable $e) {
            // --- Log failure in CronLog ---
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
