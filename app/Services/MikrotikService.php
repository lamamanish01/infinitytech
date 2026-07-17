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

                // 1. Get active sessions
                $sessions = $client->query(
                    (new Query('/ppp/active/print'))
                        ->where('name', $username)
                )->read();

                if (empty($sessions)) {
                    // No active session – try to construct interface from username
                    // but if the user isn't connected, we can't monitor.
                    continue;
                }

                foreach ($sessions as $session) {
                    $id = $session['.id'] ?? null;
                    $interface = trim($session['interface'] ?? '');

                    // If interface is missing, build it from username (common convention)
                    if (empty($interface)) {
                        $interface = "<pppoe-{$username}>";
                        Log::info("Interface missing from session, using constructed: {$interface}");
                    }

                    if (!$id) {
                        continue;
                    }

                    Log::info("Session: ID={$id}, interface='{$interface}'");

                    // 2. Try /interface monitor-traffic once
                    $rateRx = 0;
                    $rateTx = 0;
                    try {
                        $monitorQuery = (new Query('/interface/monitor-traffic'))
                            ->equal('interface', $interface)   // send as-is
                            ->equal('once', 'yes');

                        $monitorResult = $client->query($monitorQuery)->read();

                        Log::info("Monitor result for {$interface}: " . json_encode($monitorResult));

                        if (!empty($monitorResult) && isset($monitorResult[0]['rx-bits-per-second'])) {
                            $rateRx = (int) $monitorResult[0]['rx-bits-per-second'];
                            $rateTx = (int) $monitorResult[0]['tx-bits-per-second'];
                            Log::info("Monitor success: RX={$rateRx}, TX={$rateTx}");
                        } else {
                            Log::warning("Monitor returned empty for {$interface}");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Monitor exception for {$interface}: " . $e->getMessage());
                    }

                    // 3. If monitor gives 0, fallback to byte-counter (only with previous data)
                    if ($rateRx === 0 && $rateTx === 0) {
                        $currentRx = (int) ($session['rx-byte'] ?? 0);
                        $currentTx = (int) ($session['tx-byte'] ?? 0);
                        $now = microtime(true);

                        $cacheKey = 'ppp_traffic_' . $id;
                        $prev = Cache::get($cacheKey);

                        if ($prev) {
                            $timeDiff = $now - $prev['time'];
                            if ($timeDiff >= 0.5) {
                                $rateRx = (($currentRx - $prev['rx']) / $timeDiff) * 8;
                                $rateTx = (($currentTx - $prev['tx']) / $timeDiff) * 8;
                                $rateRx = max(0, $rateRx);
                                $rateTx = max(0, $rateTx);
                                Log::info("Fallback byte-counter: RX={$rateRx}, TX={$rateTx}");
                            }
                        }

                        // Store for next poll
                        Cache::put($cacheKey, [
                            'rx'   => $currentRx,
                            'tx'   => $currentTx,
                            'time' => $now,
                        ], 60);
                    }

                    $totalRx += $rateRx;
                    $totalTx += $rateTx;

                    $sessionDetails[] = [
                        'router'      => $mk->host,
                        'session_id'  => $id,
                        'interface'   => $interface,
                        'rx_rate_bps' => (int) round($rateRx),
                        'tx_rate_bps' => (int) round($rateTx),
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
            'rx_bps'   => (int) round($totalRx),
            'tx_bps'   => (int) round($totalTx),
            'sessions' => $sessionDetails,
        ];
    }
}
