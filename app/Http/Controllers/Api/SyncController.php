<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        // Optional: Validate or use request data
        $site = $request->input('site_code', 'unknown');

        // Simulate a sync result
        $result = [
            'site_code' => $site,
            'queued_records' => rand(0, 5),
            'failed_records' => rand(0, 2),
            'status' => 'ok',
            'message' => 'Data synchronized successfully.',
            'timestamp' => now()->toDateTimeString(),
        ];

        return response()->json($result);
    }
}
