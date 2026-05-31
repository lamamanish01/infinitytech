<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronJob;
use App\Models\CronLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BindMac extends Command
{
    protected $signature = 'customers:bind-mac';

    protected $description = 'Auto bind MAC from active RADIUS sessions';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            $this->info('MAC bind cron disabled');
            return Command::SUCCESS;
        }

        $bound = 0;

        try {

            Customer::whereNull('mac_address')
                ->chunkById(200, function ($customers) use (&$bound) {

                    foreach ($customers as $customer) {

                        $session = DB::table('radacct')
                            ->where('username', $customer->username)
                            ->whereNull('acctstoptime')
                            ->latest('radacctid')
                            ->first();

                        if (!$session) {
                            continue;
                        }

                        $rawMac = $session->callingstationid
                            ?? $session->callingstation_id
                            ?? null;

                        if (!$rawMac) {
                            continue;
                        }

                        $mac = $this->normalizeMac($rawMac);

                        if (!$mac) {
                            continue;
                        }

                        if (!$this->isValidMac($mac)) {
                            continue;
                        }

                        if (Customer::where('mac_address', $mac)->exists()) {
                            continue;
                        }

                        $customer->update([
                            'mac_address' => $mac
                        ]);

                        $bound++;
                    }
                });

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "{$bound} MAC addresses bound"
            ]);

            $this->info("✔ Done. {$bound} MAC addresses bound.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            $this->error("❌ " . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function normalizeMac($mac)
    {
        $mac = strtoupper(trim($mac));
        $mac = preg_replace('/[^A-F0-9]/', '', $mac);

        if (strlen($mac) !== 12) {
            return null;
        }

        return implode(':', str_split($mac, 2));
    }

    private function isValidMac($mac)
    {
        return (bool) preg_match(
            '/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/',
            $mac
        );
    }
}
