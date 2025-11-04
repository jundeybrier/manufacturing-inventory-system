<div class="flex h-8 items-center justify-center rounded-md text-accent-foreground p-1">
    <x-app-logo-icon class="h-5 w-auto fill-current text-white dark:text-black" />
</div>

<div class="ms-1 grid flex-1 text-start text-sm leading-tight">
    <span class="truncate font-semibold text-white dark:text-zinc-100">Cashiering System v2</span>
    <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
        {{ config('app.office_name') ?? 'Site ' . config('app.site_code') }}
    </span>
</div>
