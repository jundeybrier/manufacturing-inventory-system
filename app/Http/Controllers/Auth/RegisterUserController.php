<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

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
        ]);

        try {
            $response = Http::post(config('services.server.url') . '/api/register-user', $data);

            if (!$response->successful()) {
                throw ValidationException::withMessages([
                    'email' => ['Registration failed. Server error.'],
                ]);
            }

            return redirect()->route('registration')->with('status', 'Registration submitted. Please wait for activation.');

        } catch (Throwable $e) {
            // Optional: log the error
            Log::error('Registration sync failed: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Registration service is currently unavailable. Please try again later.',
            ])->withInput();
        }
    }
}
