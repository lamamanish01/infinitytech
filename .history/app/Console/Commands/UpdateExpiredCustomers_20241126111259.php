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
        $today = Carbon::todday()->toDateString();

        RadCheck::where('attribute', 'Expiration')
            ->where('value', '<', $today)
            ->update(['attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Reject']);

        $this->info('Expired Users have been ')
    }
}
