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
if (! function_exists('current_office_name')) {
    /**
     * Returns the Office Name matching the APP_SITE_CODE (or UUID)
     */
    function current_office_name()
    {
        $code = config('app.site_code') ?? config('app.office_uuid');

        if (! $code) {
            return null;
        }

        return \App\Models\Office::where('uuid', $code)
            ->value('name') ?? 'Unknown Office';
    }
}
