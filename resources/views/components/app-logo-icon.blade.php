<img
    src="{{ asset('images/logo-light.png') }}"
    alt="Logo"
    {{ $attributes->merge(['class' => 'block dark:hidden h-10 w-auto']) }} />

<img
    src="{{ asset('images/logo-dark.png') }}"
    alt="Logo"
    {{ $attributes->merge(['class' => 'hidden dark:block h-10 w-auto']) }} />
