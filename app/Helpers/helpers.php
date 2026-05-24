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
