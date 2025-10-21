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
            'payload' => $request->except(['password', 'password_confirmation']),
        ]);

        try {
            // Step 2 — Basic validation (no unique yet)
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|email',
                'password'  => 'required|string|min:8',
                'site_code' => 'required|string',
            ]);

            // Step 3 — Check if account already exists
            $existingUser = User::where('email', $validated['email'])->first();

            if ($existingUser) {
                Log::info('[ClientRegistration] Existing user found, returning for sync', [
                    'user_id' => $existingUser->id,
                    'email'   => $existingUser->email,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Account already exists — returning existing record for sync.',
                    'client'  => [
                        'id'         => $existingUser->id,
                        'uuid'       => $existingUser->uuid,
                        'name'       => $existingUser->name,
                        'email'      => $existingUser->email,
                        'password'   => $existingUser->password,
                        'site_code'  => $existingUser->office_id ?? $validated['site_code'],
                    ],
                ], 200);
            }

            // Step 4 — Generate client UUID
            $clientUuid = (string) Str::uuid();

            // Step 5 — Create new user
            $user = User::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'office_id'     => $validated['site_code'],
                'branch_id'     => $validated['site_code'],
                'is_client_user'=> true,
                'uuid'          => $clientUuid,
            ]);

            Log::info('[ClientRegistration] User created successfully', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'uuid'    => $clientUuid,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client registered successfully.',
                'client'  => [
                    'id'         => $user->id,
                    'uuid'       => $clientUuid,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'password'   => $user->password,
                    'site_code'  => $validated['site_code'],
                ],
            ], 201);

        } catch (Throwable $e) {
            Log::error('[ClientRegistration] Error occurred', [
                'timestamp' => now()->toDateTimeString(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

}
