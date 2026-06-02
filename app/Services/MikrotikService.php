<?php

namespace App\Services;

use App\Models\Mikrotik;
use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    private static function client($mk)
    {
        return new Client([
            'host' => $mk->host,
            'user' => $mk->username,
            'pass' => $mk->password,
            'port' => $mk->port ?? 8728,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DISCONNECT PPPoE USER
    |--------------------------------------------------------------------------
    */

    public static function disconnectPPPoE($username)
    {
        try {

            $routers = Mikrotik::where('is_active', 1)->get();

            $found = false;
            $removed = 0;

            foreach ($routers as $mk) {

                try {

                    $client = self::client($mk);

                    $sessions = $client->query(
                        (new Query('/ppp/active/print'))
                            ->where('name', $username)
                    )->read();

                    if (empty($sessions)) {
                        continue;
                    }

                    $found = true;

                    foreach ($sessions as $session) {

                        if (empty($session['.id'])) {
                            continue;
                        }

                        try {

                            $client->query(
                                (new Query('/ppp/active/remove'))
                                    ->equal('.id', $session['.id'])
                            )->read();

                            $removed++;

                        } catch (\Throwable $e) {

                            Log::error('Mikrotik session remove failed', [
                                'username' => $username,
                                'session' => $session,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                } catch (\Throwable $e) {

                    Log::error('Mikrotik router connection failed', [
                        'router' => $mk->host,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (!$found) {
                return [
                    'status' => false,
                    'message' => 'User not found on any MikroTik',
                ];
            }

            if ($removed === 0) {
                return [
                    'status' => false,
                    'message' => 'Session found but could not be removed',
                ];
            }

            return [
                'status' => true,
                'message' => "User disconnected successfully {$removed} session",
            ];

        } catch (\Throwable $e) {

            Log::error('Mikrotik disconnect fatal error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
