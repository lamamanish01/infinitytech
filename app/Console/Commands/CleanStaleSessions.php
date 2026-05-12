<?php

namespace App\Console\Commands;

use App\Models\RadAcct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanStaleSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:clean-stale-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove stale sessions from the FreeRADIUS radacct table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->where('acctstarttime', '<', now()->subMinutes(5))
            ->update([
                'acctstoptime' => now(),
                'acctterminatecause' => 'Stale-Session-Cleanup'
            ]);

        $this->info("Cleaned {$updated} stale sessions.");
    }
}
