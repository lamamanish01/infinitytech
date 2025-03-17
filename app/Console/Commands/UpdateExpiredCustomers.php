<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Nas;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\Recharge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExpiredCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update exipred customers & change  Framed-Pool in radreply';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $expiredCustomer = RadCheck::where('attribute', 'Expiration')
            ->where('value', '<', $today)
            ->pluck('username');

        if ($expiredCustomer->isEmpty())
        {
            return $this->info('No Expired Customer Found');
        }
        RadReply::whereIn('username', $expiredCustomer)
            ->where('attribute', 'Session-Timeout')
            ->delete();

            foreach ($expiredCustomer as $username) {
                RadReply::updateOrInsert(
                    [
                        'username' => $username,
                        'attribute' => 'Session-Timeout',
                    ],
                    [
                        'op' => ':=',
                        'value' => '0', // Force session to expire immediately
                    ]
                );
            }
    }
}
