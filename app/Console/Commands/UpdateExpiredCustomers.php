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
    // public function handle()
    // {
    //     $expiredUsers = RadCheck::where('attribute', 'Expiration')->get();

    //     foreach($expiredUsers as $expiredUser) {
    //         $expiredAt = Carbon::createFromFormat('d M Y H:i:s', $expiredUser->value);

    //         if($expiredAt->isPast()) {
    //             RadCheck::updateOrCreate(
    //                 ['username' => $expiredUser->username, 'attribute' => 'Auth-Type'],
    //                 ['op' => ':=', 'value' => 'Reject']
    //             );
    //             $this->info("User {$expiredUser->username} marked as expired.");
    //         }
    //     }
    //             $this->info('Done disabling expired users.');
    // }

    public function handle()
{
    $this->info('Checking for expired users...');

    $expiredUsers = RadCheck::where('attribute', 'Expiration')->get();

    foreach ($expiredUsers as $expiredUser) {
        try {
            $expiredAt = Carbon::createFromFormat('d M Y H:i:s', $expiredUser->value);

            if ($expiredAt->isPast()) {
                RadCheck::updateOrCreate(
                    [
                        'username'  => $expiredUser->username,
                        'attribute' => 'Auth-Type'
                    ],
                    [
                        'op'    => ':=',
                        'value' => 'Reject'
                    ]
                );

                $this->info("User {$expiredUser->username} marked as expired.");
            }
        } catch (\Exception $e) {
            $this->error("Failed to process user {$expiredUser->username}: " . $e->getMessage());
        }
    }
    $this->info('Done disabling expired users.');
}

}
