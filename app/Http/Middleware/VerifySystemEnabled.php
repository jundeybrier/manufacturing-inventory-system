<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Office;
use Symfony\Component\HttpFoundation\Response;

class VerifySystemEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $siteCode = config('app.site_code');

        // If site_code is missing, disable access immediately
        if (empty($siteCode)) {
            return $this->deny('System disabled: APP_SITE_CODE not set.');
        }

        // Check if site code exists in offices table
        $exists = Office::where('uuid', $siteCode)->exists();

        if (! $exists) {
            return $this->deny("System disabled: Site not registered in offices table.");
        }

        // Otherwise, system is valid
        return $next($request);
    }

    /**
     * Return a consistent denial response (Blade or JSON)
     */
    private function deny(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => $message], 403);
        }

        return response()->view('errors.system-disabled', [
            'message' => $message,
        ], 403);
    }
}
