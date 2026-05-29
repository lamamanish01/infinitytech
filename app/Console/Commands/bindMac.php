<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BindMac extends Command
{
    protected $signature = 'customers:bind-mac';

    protected $description = 'Auto bind MAC from active RADIUS sessions';

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

                        if (!$session) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | GET MAC
                        |--------------------------------------------------------------------------
                        */

                        $mac = $session->callingstationid
                            ?? $session->callingstation_id
                            ?? null;

                        if (!$mac) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NORMALIZE
                        |--------------------------------------------------------------------------
                        */

                        $mac = $this->normalizeMac($mac);

                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATE
                        |--------------------------------------------------------------------------
                        */

                        if (!$this->isValidMac($mac)) {
                            $this->warn("Invalid MAC for {$customer->username}: {$mac}");
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE CUSTOMER
                        |--------------------------------------------------------------------------
                        */

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
                'message' => "{$bound} MAC addresses bound"
            ]);

            $this->info("Done. {$bound} MAC addresses bound.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE MAC
    |--------------------------------------------------------------------------
    */

    private function normalizeMac($mac)
    {
        $mac = strtolower($mac);

        // remove separators
        $mac = str_replace(['-', '.', ':'], '', $mac);

        // validate raw length
        if (strlen($mac) !== 12) {
            return $mac;
        }

        // convert to aa:bb:cc:dd:ee:ff
        return implode(':', str_split($mac, 2));
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE MAC
    |--------------------------------------------------------------------------
    */

    private function isValidMac($mac)
    {
        return (bool) preg_match(
            '/^([0-9a-f]{2}:){5}[0-9a-f]{2}$/i',
            $mac
        );
    }
}
