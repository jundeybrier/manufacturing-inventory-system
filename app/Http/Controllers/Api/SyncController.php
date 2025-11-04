<?php

namespace App\Http\Controllers\Api;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        // Step 1: Get all offices from central DB
        $expectedToken = config('services.server.token');
        $providedToken = $request->bearerToken();

        if (! $expectedToken || $providedToken !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid or missing token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Optional: Validate or use request data
        $offices = Office::all(['uuid','name', 'location', 'created_at', 'updated_at']);

        // Step 2: Package response
        return response()->json([
            'records' => $offices,
            'count' => $offices->count(),
            'status' => 'ok',
            'message' => 'Offices synchronized successfully.',
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json($result);
    }
}
