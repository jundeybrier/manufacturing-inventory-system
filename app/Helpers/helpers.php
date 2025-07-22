<?php

if (!function_exists('isWindowsShareReachable')) {
    function isWindowsShareReachable($path)
    {
        return file_exists($path);
    }
}
