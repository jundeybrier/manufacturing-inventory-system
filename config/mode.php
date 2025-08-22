<?php

return [
    'type' => env('APP_MODE', 'client'),

    'is_server' => env('APP_MODE', 'client') === 'server',
    'is_client' => env('APP_MODE', 'client') === 'client',
];
