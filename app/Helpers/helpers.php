<?php

if (!function_exists('isWindowsShareReachable')) {
    function isWindowsShareReachable($path)
    {
        return file_exists($path);
    }
}
if (!function_exists('app_is_server')) {
    function app_is_server(): bool
    {
        return config('mode.type') === 'server';
    }
}

if (!function_exists('app_is_client')) {
    function app_is_client(): bool
    {
        return config('mode.type') === 'client';
    }
}

