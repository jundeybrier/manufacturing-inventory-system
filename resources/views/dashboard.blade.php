<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                @livewire('transactions.session-status')
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="p-5 relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="text-right text-zinc-500 dark:text-zinc-300">
                    {{ now()->format('l, F j, Y') }}
                </div>
                <div class="text-center pt-6">
                    <h1 class="text-3xl font-extrabold animated-gradient bg-gradient-to-r from-blue-600 via-cyan-400 to-blue-600 bg-clip-text text-transparent tracking-wide">
                        Cashiering System v2
                    </h1>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Exclusively made for the Department of Foreign Affairs only
                    </p>
                </div>
                @livewire('stress-button')
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            @livewire('charts.transactions-sync-chart')
        </div>
    </div>
</x-layouts.app>
