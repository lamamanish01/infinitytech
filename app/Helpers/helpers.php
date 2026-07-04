<?php

use App\Services\MacService;

if (!function_exists('normalize_mac')) {
    function normalize_mac($mac)
    {
        return MacService::normalize($mac);
    }
}

if (!function_exists('get_active_mac')) {
    function get_active_mac($username)
    {
        return MacService::getActiveMac($username);
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
