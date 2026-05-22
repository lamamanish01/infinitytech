<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BindMac extends Command
{
    protected $signature = 'customers:bind-mac';

    protected $description = 'Auto bind missing MAC from active RADIUS sessions';

    private function normalizeMac($mac)
    {
        return strtoupper(str_replace([':', '-', '.'], '', $mac));
    }

    public function handle()
    {
        Customer::whereNull('mac_address')
            ->chunkById(200, function ($customers) {

                foreach ($customers as $customer) {

                    $session = DB::table('radacct')
                        ->where('username', $customer->username)
                        ->whereNull('acctstoptime')
                        ->latest()
                        ->first();

                    if (!$session || !$session->callingstationid) {
                        continue;
                    }

                    $mac = $this->normalizeMac($session->callingstationid);

                    // avoid invalid empty MAC
                    if (!$mac) {
                        continue;
                    }

                    $customer->update([
                        'mac_address' => $mac
                    ]);

                    $this->info("MAC bound: {$customer->username} => {$mac}");
                }
            });

        return Command::SUCCESS;
    }
}
