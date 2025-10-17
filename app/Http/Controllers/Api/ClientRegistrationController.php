<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ClientRegistrationController extends Controller
{
    public function store(Request $request)
    {
        Log::info('[ClientRegistration] Step 1: Received registration request', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'payload' => $request->except(['password', 'password_confirmation']),
        ]);

        try {
            // Step 2 — Validation
            Log::info('[ClientRegistration] Step 2: Validating input');

            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|string|min:8',
                'site_code' => 'required|string',
            ]);

            // Step 3 — Generate client UUID
            $clientUuid = (string) Str::uuid();
            Log::info('[ClientRegistration] Step 3: Generated client UUID', [
                'uuid' => $clientUuid,
            ]);

            // Step 4 — Create user record
            Log::info('[ClientRegistration] Step 4: Creating new user record', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'site_code' => $validated['site_code'],
            ]);

            $user = User::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'office_id'     => $validated['site_code'],
                'branch_id'     => $validated['site_code'],
                'is_client_user'=> true,
                'uuid'   => $clientUuid, // 🆕 store the UUID
            ]);

            // Step 5 — Success
            Log::info('[ClientRegistration] Step 5: User created successfully', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'uuid'    => $clientUuid,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client registered successfully',
                'client'  => [
                    'id'         => $user->id,
                    'uuid'       => $clientUuid,
                    'name'       => $user->name,
                    'password'   => $user->password,
                    'email'      => $user->email,
                    'site_code'  => $validated['site_code'],
                ],
            ], 201);

        } catch (Throwable $e) {
            Log::error('[ClientRegistration] Error occurred', [
                'timestamp' => now()->toDateTimeString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
