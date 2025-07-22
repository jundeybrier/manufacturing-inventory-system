@props(['model'])

<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-cloak
    x-show="open"
    class="fixed inset-0 z-50 flex justify-end"
    role="dialog"
    aria-modal="true"
>
    <!-- Background overlay with fade -->
    <div
        class="absolute inset-0 bg-black/30 dark:bg-black/50 transition-opacity duration-300"
        x-show="open"
        x-transition.opacity
        x-on:click="open = false"
    ></div>

    <!-- Slide-over panel with smooth transform -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        x-on:click.away="open = false"
        class="relative z-50 w-full max-w-md h-full bg-white dark:bg-zinc-900 shadow-xl border-l border-zinc-200 dark:border-zinc-700 overflow-y-auto"
    >
        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
</div>
