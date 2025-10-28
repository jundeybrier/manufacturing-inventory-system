<x-layouts.auth :title="__('Create an account')">
    <div class="flex flex-col gap-6 max-w-md mx-auto">
        <h2 class="text-xl font-semibold text-center">
            {{ __('Create an account') }}
        </h2>
        <p class="text-sm text-center text-zinc-600 dark:text-zinc-400">
            {{ __('Enter your details below to create your account') }}
        </p>

        <div class="mt-4 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-zinc-300/50
                bg-zinc-100/60 dark:bg-zinc-800/60 dark:border-zinc-700/50
                text-xs text-zinc-600 dark:text-zinc-400 shadow-sm">
                <span class="uppercase tracking-wide font-medium">
            Running in
            <span class="text-emerald-600 dark:text-emerald-400">
                {{ ucfirst(config('app.mode')) }} Mode
            </span>
        </span>
                @if (config('app.site_code'))
                    <span class="ml-2 text-[10px] text-zinc-400">
                (Site {{ config('app.site_code') }})
            </span>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="text-green-600 text-sm text-center">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('client.register.store') }}" class="flex flex-col gap-4">
            @csrf
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
