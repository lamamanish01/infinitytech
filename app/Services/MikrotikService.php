<?php

namespace App\Services;

use App\Models\Mikrotik;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

    public static function getPPPUserTraffic($username)
    {
        $routers = Mikrotik::where('is_active', 1)->get();
        $totalRx = 0;
        $totalTx = 0;
        $sessionDetails = [];

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

                foreach ($sessions as $session) {
                    $id = $session['.id'] ?? null;
                    if (!$id) {
                        continue;
                    }

                    // Current cumulative byte counters
                    $currentRx = (int) ($session['rx-byte'] ?? 0);
                    $currentTx = (int) ($session['tx-byte'] ?? 0);
                    $now = microtime(true);

                    $cacheKey = 'ppp_traffic_' . $id;
                    $prev = Cache::get($cacheKey);

                    $rateRx = 0;
                    $rateTx = 0;

                    if ($prev) {
                        $timeDiff = $now - $prev['time'];
                        if ($timeDiff >= 0.5) {  // avoid division by zero
                            // bytes → bits per second
                            $rateRx = (($currentRx - $prev['rx']) / $timeDiff) * 8;
                            $rateTx = (($currentTx - $prev['tx']) / $timeDiff) * 8;
                            $rateRx = max(0, $rateRx);
                            $rateTx = max(0, $rateTx);
                        }
                    }

                    // Store for next poll
                    Cache::put($cacheKey, [
                        'rx'   => $currentRx,
                        'tx'   => $currentTx,
                        'time' => $now,
                    ], 60);

                    $totalRx += $rateRx;
                    $totalTx += $rateTx;

                    $sessionDetails[] = [
                        'router'      => $mk->host,
                        'session_id'  => $id,
                        'rx_rate_bps' => round($rateRx, 2),
                        'tx_rate_bps' => round($rateTx, 2),
                        'address'     => $session['address'] ?? null,
                        'uptime'      => $session['uptime'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('PPP traffic fetch failed', [
                    'router'   => $mk->host,
                    'username' => $username,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return [
            'rx_bps'   => round($totalRx, 2),
            'tx_bps'   => round($totalTx, 2),
            'sessions' => $sessionDetails,
        ];
    }
}
