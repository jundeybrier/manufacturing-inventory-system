<x-layouts.auth :title="__('Create an account')">
    <div class="flex flex-col gap-6 max-w-md mx-auto">
        <h2 class="text-xl font-semibold text-center">
            {{ __('Create an account') }}
        </h2>
        <p class="text-sm text-center text-zinc-600 dark:text-zinc-400">
            {{ __('Enter your details below to create your account') }}
        </p>

        @if (session('status'))
            <div class="text-green-600 text-sm text-center">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('client.register.store') }}" class="flex flex-col gap-4">
            @csrf
            <!-- Site Code -->
            <flux:input
                name="site_code"
                :label="__('Site Code')"
                type="text"
                required
                placeholder="e.g. CO-BUTUAN"
                value="{{ old('site_code') }}"
            />
            @error('site_code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Full name"
                value="{{ old('name') }}"
            />
            @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror

            <!-- Email -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
                value="{{ old('email') }}"
            />
            @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Password"
                viewable
            />
            @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirm Password"
                viewable
            />

            <!-- Submit Button -->
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="text-sm text-center text-zinc-600 dark:text-zinc-400 mt-4">
            {{ __('Already have an account?') }}
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
