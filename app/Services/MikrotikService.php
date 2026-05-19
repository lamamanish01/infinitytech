<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikroTikService
{
    public function connect($mk)
    {
        return new Client([
            'host' => $mk->host,
            'user' => $mk->username,
            'pass' => $mk->password,
            'port' => $mk->port,
        ]);
    }

    public function disconnectPPPoE($mk, $username)
    {
        $client = $this->connect($mk);

        $sessions = $client->query(
            (new Query('/ppp/active/print'))
                ->where('name', $username)
        )->read();

        foreach ($sessions as $s) {
            $client->query(
                (new Query('/ppp/active/remove'))
                    ->equal('.id', $s['.id'])
            )->read();
        }
    }
}
