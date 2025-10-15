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
            'ip' => $request->ip(),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        Log::info('Input validated successfully.', [
            'email' => $data['email'],
            'site_code' => config('app.site_code'),
        ]);

        try {
            if (config('app.mode') === 'client') {
                // 🔹 Step 1: Send request to server
                $serverUrl = config('services.server.url') . '/api/register-user';
                Log::info('Attempting to register on central server.', [
                    'server_url' => $serverUrl,
                    'payload' => [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'site_code' => config('app.site_code')
                    ],
                ]);

                $response = Http::withToken(config('services.server.token'))
                    ->timeout(15)
                    ->post($serverUrl, $data);

                // 🔹 Step 2: Log raw response
                Log::info('Server responded.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if (!$response->successful()) {
                    Log::warning('Server registration failed.', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    throw ValidationException::withMessages([
                        'email' => ['Registration failed: ' . $response->body()],
                    ]);
                }

                $remoteUser = $response->json('user');
                Log::info('Server returned user record.', [
                    'remote_user_id' => $remoteUser['id'] ?? null,
                    'remote_email' => $remoteUser['email'] ?? null,
                ]);

                // 🔹 Step 3: Mirror the record locally
                $localUser = User::create([
                    'id' => $remoteUser['id'] ?? null,
                    'name' => $remoteUser['name'] ?? $data['name'],
                    'email' => $remoteUser['email'] ?? $data['email'],
                    'password' => Hash::make($data['password']),
                    'site_code' => $remoteUser['site_code'] ?? $data['site_code'],
                    'is_synced' => true,
                ]);

                Log::info('Local user created successfully after server sync.', [
                    'local_user_id' => $localUser->id,
                    'email' => $localUser->email,
                ]);

            } else {
                // 🔹 Step 4: Register directly in server mode
                Log::info('Operating in server mode. Creating local user only.');

                $localUser = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'site_code' => config('app.site_code'),
                ]);

                Log::info('Local user created successfully (server mode).', [
                    'local_user_id' => $localUser->id,
                    'email' => $localUser->email,
                ]);
            }

            Log::info('Registration completed successfully.');

            return redirect()->route('login')
                ->with('status', 'Registration successful. You may now log in.');

        } catch (Throwable $e) {
            Log::error('Registration process failed.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'email' => 'Server unavailable. Please try again later.',
            ])->withInput();
        }
    }
}
