<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegisterUserController extends Controller
{
    public function show()
    {
        Log::info('Displaying client registration form.');
        return view('auth.client-register');
    }

    public function store(Request $request)
    {
        Log::info('Registration attempt started.', [
            'mode' => config('app.mode'),
            'ip'   => $request->ip(),
        ]);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $data['site_code'] = config('app.site_code');

        Log::info('Input validated successfully.', [
            'email'     => $data['email'],
            'site_code' => $data['site_code'],
        ]);

        try {
            if (config('app.mode') === 'client') {
                // 🔹 Step 1: Send registration request to central server
                $serverUrl = config('services.server.url') . '/api/register-user';

                Log::info('Attempting to register on central server.', [
                    'server_url' => $serverUrl,
                    'payload'    => [
                        'name'      => $data['name'],
                        'email'     => $data['email'],
                        'site_code' => $data['site_code'],
                    ],
                ]);

                $response = Http::withToken(config('services.server.token'))
                    ->when(config('services.server.verify_ssl', true) === false, fn($http) => $http->withoutVerifying())
                    ->withHeaders(['Accept' => 'application/json'])
                    ->timeout(15)
                    ->post($serverUrl, $data);

                Log::info('Server responded.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if (! $response->successful()) {
                    Log::warning('Server registration failed.', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);

                    $message = 'Registration failed. Please try again.';

                    // If the server returned JSON (Laravel-style)
                    if ($response->json('message')) {
                        $message = $response->json('message');
                    } elseif ($response->json('errors.email.0')) {
                        $message = $response->json('errors.email.0');
                    } elseif ($response->status() === 409) {
                        $message = 'This email is already registered.';
                    }

                    throw ValidationException::withMessages([
                        'email' => [$message],
                    ]);
                }

                // 🔹 Step 2: Parse central server response
                $remoteUser = $response->json('client');
                $uuid       = $remoteUser['uuid'] ?? null;

                Log::info('Server returned client record.', [
                    'remote_user_id' => $remoteUser['id'] ?? null,
                    'uuid'           => $uuid,
                ]);

                // 🔹 Step 3: Mirror record locally
                $localUser = User::create([
                    'id'            => $remoteUser['id'] ?? null,
                    'uuid'          => $uuid, // 🆕 use built-in uuid field
                    'name'          => $remoteUser['name'] ?? $data['name'],
                    'email'         => $remoteUser['email'] ?? $data['email'],
                    'password'      => $remoteUser['password'] ?? Hash::make($data['password']),
                    'site_code'     => $remoteUser['site_code'] ?? $data['site_code'],
                    'is_client_user'=> true,
                    'is_synced'     => true,
                ]);

                Log::info('Local user created successfully after server sync.', [
                    'local_user_id' => $localUser->id,
                    'uuid'          => $uuid,
                ]);

            } else {
                // 🔹 Step 4: Server mode — create user locally only
                Log::info('Operating in server mode. Creating local user only.');

                $localUser = User::create([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'password'  => Hash::make($data['password']),
                    'site_code' => $data['site_code'],
                ]);

                Log::info('Local user created successfully (server mode).', [
                    'local_user_id' => $localUser->id,
                    'email'         => $localUser->email,
                ]);
            }

            Log::info('Registration completed successfully.');

            return redirect()->route('login')
                ->with('status', 'Registration successful. You may now log in.');

        } catch (Throwable $e) {
            Log::error('Registration process failed.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'email' => $e instanceof ValidationException
                    ? $e->errors()['email'][0]
                    : 'Server unavailable. Please try again later.',
            ])->withInput();
        }
    }
}
