<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->resetErrorBag(); // clear any old errors from session or redirects

        try {
            // 🔹 Local validation
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ]);

            // 🔹 Add site code if needed
            $validated['site_code'] = config('app.site_code');
            $validated['password'] = Hash::make($validated['password']);

            // 🔹 If in "client" mode — register via remote server
            if (config('app.mode') === 'client') {
                $serverUrl = config('services.server.url') . '/api/register-user';
                $response = Http::withToken(config('services.server.token'))
                    ->when(config('services.server.verify_ssl', true) === false, fn($http) => $http->withoutVerifying())
                    ->withHeaders(['Accept' => 'application/json'])
                    ->timeout(15)
                    ->post($serverUrl, $validated);

                if (! $response->successful()) {
                    // Extract readable message from API
                    $message = $response->json('errors.email.0')
                        ?? $response->json('message')
                        ?? ($response->status() === 409
                            ? 'The email has already been taken.'
                            : 'Registration failed. Please try again.');

                    throw ValidationException::withMessages([
                        'email' => [$message],
                    ]);
                }

                $remoteUser = $response->json('client');
                $validated['uuid'] = $remoteUser['uuid'] ?? null;
            }

            // 🔹 Create local user
            $user = User::create($validated);

            event(new Registered($user));
            Auth::login($user);

            $this->redirectIntended(route('dashboard', absolute: false), navigate: true);

        } catch (ValidationException $e) {
            // Show only the first relevant error
            $this->addError('email', collect($e->errors()['email'] ?? [$e->getMessage()])->unique()->first());
        } catch (\Throwable $e) {
            // Generic catch for timeout, SSL, etc.
            $this->addError('email', 'Unable to connect to server. Please try again later.');
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
            :show-errors="false"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
