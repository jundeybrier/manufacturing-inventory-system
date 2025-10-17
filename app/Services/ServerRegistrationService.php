<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ServerRegistrationService
{
    public static function registerToServer($payload)
    {
        $serverUrl = config('services.server.url') . '/api/client-register';
        $token = config('services.server.token');
        $verifySsl = config('services.server.verify_ssl', true); // default: true

        // Base HTTP client
        $http = Http::withToken($token);

        // If SSL verification is disabled (e.g., self-signed cert via VPN)
        if (! $verifySsl) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($serverUrl, $payload);

        if ($response->successful()) {
            return $response->json()['user'] ?? null;
        }

        throw new \Exception('Server registration failed: ' . $response->body());
    }
}
