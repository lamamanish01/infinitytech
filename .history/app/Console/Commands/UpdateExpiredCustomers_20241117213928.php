<?php

namespace App\Console\Commands;

use App\Models\RadCheck;
use Illuminate\Console\Command;

class UpdateExpiredCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-expired-customers';

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
        $expiredCustomers = RadCheck::where('Expiration', '<', now())->get();

        if ($expiredCustomers->isEmpty())
        {
            $this->info('No expired customers were found.');
        }

        foreach ($expiredCustomers as $$expiredCustomer)
        {
            RadReply::updateOrCreate([
                'username' => $expiredCustomer,
                'attribute' => 'Framed-Pool'
                ''
            ]);
        }
    }
}
