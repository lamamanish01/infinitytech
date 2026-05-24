<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BindMac extends Command
{
    protected $signature = 'customers:bind-mac';
    protected $description = 'Auto bind missing MAC from active RADIUS sessions';

    public function handle()
    {
        $bound = 0;

        try {

            Customer::whereNull('mac_address')
                ->chunkById(200, function ($customers) use (&$bound) {

                    foreach ($customers as $customer) {

                        $session = DB::table('radacct')
                            ->where('username', $customer->username)
                            ->whereNull('acctstoptime')
                            ->orderByDesc('radacctid')
                            ->first();

                        // 🔥 FIX: correct field name fallback
                        $mac = $session->callingstationid ?? $session->callingstation_id ?? null;

                        if (!$mac) {
                            continue;
                        }

                        $mac = $this->normalizeMac($mac);

                        if (strlen($mac) < 10) {
                            continue;
                        }

                        $customer->update([
                            'mac_address' => $mac
                        ]);

                        $bound++;

                        $this->info("MAC bound: {$customer->username} => {$mac}");
                    }
                });

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "MAC bound for {$bound} customers"
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }

}
