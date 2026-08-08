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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:discontinue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discontinue customers with no recharge in 3 months (via RADIUS)';

    /**
     * Execute the console command.
     */
    public function handle(RadiusService $radiusService): int
    {
        // --- Check cron job configuration ---
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job configuration not found for: {$this->signature}");
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->error("Cron job '{$this->signature}' is inactive. Skipping execution.");
            return self::FAILURE;
        }

        // --- Statistics counters ---
        $discontinued = 0;
        $failed = 0;
        $skipped = 0;
        $cutoff = Carbon::now()->subMonths(3);

        try {
            // Process eligible customers in chunks to avoid memory overload
            Customer::where('status', '!=', 'discontinued')
                    ->where('expire_date', '<', $cutoff)
                    ->chunkById(200, function ($customers) use ($radiusService, &$discontinued, &$failed, &$skipped) {
                        foreach ($customers as $customer) {
                            try {
                                // Call the RADIUS service – it updates status and deletes RADIUS entries
                                $radiusService->discontinueCustomer($customer);

                                // If we get here, no exception was thrown → success
                                $discontinued++;
                                $this->info("✓ Customer #{$customer->id} discontinued.");

                            } catch (\Throwable $e) {
                                $failed++;
                                Log::error('Discontinuation failed for customer ' . $customer->id, [
                                    'error' => $e->getMessage(),
                                ]);
                                $this->error("✗ Failed for #{$customer->id}: " . $e->getMessage());
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
