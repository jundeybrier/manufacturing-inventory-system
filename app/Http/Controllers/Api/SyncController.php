<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        // Step 1: Token validation
        $expectedToken = config('services.server.token');
        $providedToken = $request->bearerToken();

        if (! $expectedToken || $providedToken !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid or missing token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Step 2: Expect UUID from client
        $uuid = $request->input('uuid');

        if (! $uuid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameter: uuid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Step 3: Find the office by UUID
        $office = Office::where('uuid', $uuid)->first();

        if (! $office) {
            return response()->json([
                'status' => 'error',
                'message' => 'Office not found for provided UUID.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Step 4: Get all offices (for reference) and users under this office
        $offices = Office::all(['uuid', 'name', 'location', 'created_at', 'updated_at']);

        $users = User::where('office_id', $office->id)
            ->select('id','password', 'name', 'email', 'office_id', 'created_at', 'updated_at')
            ->get();

        // Step 5: Package response
        return response()->json([
            'status' => 'ok',
            'message' => 'Sync completed successfully.',
            'timestamp' => now()->toDateTimeString(),
            'records' => [
                'offices' => $offices,
                'users' => $users,
            ],
            'counts' => [
                'offices' => $offices->count(),
                'users' => $users->count(),
            ],
        ]);
    }
}
