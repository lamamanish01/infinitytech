<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $users = DB::connection('radius')->table('radacct')
            ->select('username')
            ->whereNull('AcctStopTime')  // Active sessions (no stop time)
            ->groupBy('username')
            ->havingRaw('COUNT(*) > 1') // More than one active session
            ->get();

            foreach ($users as $user) {
                // Keep only the latest session and mark others as stale
                DB::connection('radius')->table('radacct')
                    ->whereNull('AcctStopTime')
                    ->where('username', $user->username)
                    ->whereNotIn('radacctid', function ($query) use ($user) {
                        $query->select('radacctid')
                              ->from('radacct')
                              ->where('username', $user->username)
                              ->whereNull('AcctStopTime')
                              ->orderBy('AcctStartTime', 'DESC')
                              ->limit(1); // Keep the latest session
                    })
                    ->update([
                        'AcctStopTime' => now(),          // Mark session as stopped
                        'AcctTerminateCause' => 'Stale',  // Reason for stop
                    ]);
                }
    }
}
