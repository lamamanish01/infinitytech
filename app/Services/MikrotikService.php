<?php

namespace App\Services;

use App\Models\Mikrotik;
use RouterOS\Client;
use RouterOS\Query;

class MikroTikService
{
    private static function client()
    {
        /*
        |--------------------------------------------------------------------------
        | GET ACTIVE ROUTER
        |--------------------------------------------------------------------------
        */

        $mk = Mikrotik::where('is_active', 1)->first();

        if (!$mk) {
            throw new \Exception('No active MikroTik found.');
        }

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

        $client = self::client();

        $sessions = $client->query(
            (new Query('/ppp/active/print'))
                ->where('name', $username)
        )->read();

        if (empty($sessions)) {

            return [
                'status' => false,
                'message' => 'User not online',
            ];
        }

        foreach ($sessions as $session) {

            $client->query(
                (new Query('/ppp/active/remove'))
                    ->equal('.id', $session['.id'])
            )->read();
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
