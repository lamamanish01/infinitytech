<?php

namespace App\Services;

use App\Models\Mikrotik;
use RouterOS\Client;
use RouterOS\Query;

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

            foreach ($routers as $mk) {

                $client = self::client($mk);

                // check session in this router
                $sessions = $client->query(
                    (new Query('/ppp/active/print'))
                        ->where('name', $username)
                )->read();

                if (empty($sessions)) {
                    continue;
                }

                $found = true;

                foreach ($sessions as $session) {

                    if (!isset($session['.id'])) {
                        continue;
                    }

                    $client->query(
                        (new Query('/ppp/active/remove'))
                            ->equal('.id', $session['.id'])
                    )->read();
                }
            }

            if (!$found) {
                return [
                    'status' => false,
                    'message' => 'User not found on any MikroTik',
                ];
            }

            return [
                'status' => true,
                'message' => 'User disconnected successfully',
            ];

        } catch (\Exception $e) {

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
