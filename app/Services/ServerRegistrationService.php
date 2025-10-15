<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ServerRegistrationService
{
    public static function registerToServer($payload)
    {
        $serverUrl = config('services.server.url') . '/api/client-register';
        $token = config('services.server.token'); // if you secure via token

        $response = Http::withToken($token)->post($serverUrl, $payload);

        if ($response->successful()) {
            return $response->json()['user'];
        }

        throw new \Exception('Server registration failed: ' . $response->body());
    }
}
