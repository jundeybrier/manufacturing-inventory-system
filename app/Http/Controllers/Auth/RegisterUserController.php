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
        return view('auth.client-register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'site_code' => ['required', 'string'],
        ]);

        try {
            if (config('app.mode') === 'client') {
                // 🔹 Register on the server first
                $response = Http::withToken(config('services.server.token'))
                    ->post(config('services.server.url') . '/api/register-user', $data);

                if (!$response->successful()) {
                    throw ValidationException::withMessages([
                        'email' => ['Registration failed: ' . $response->body()],
                    ]);
                }

                $remoteUser = $response->json('user');

                // 🔹 Mirror server record locally
                User::create([
                    'id' => $remoteUser['id'], // identical user_id
                    'name' => $remoteUser['name'],
                    'email' => $remoteUser['email'],
                    'password' => Hash::make($data['password']),
                    'site_code' => $remoteUser['site_code'] ?? $data['site_code'],
                    'is_synced' => true,
                ]);
            } else {
                // 🔹 Server mode — normal local registration
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'site_code' => $data['site_code'],
                ]);
            }

            return redirect()->route('login')
                ->with('status', 'Registration successful. You may now log in.');

        } catch (Throwable $e) {
            Log::error('Registration failed: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Server unavailable. Please try again later.',
            ])->withInput();
        }
    }
}
