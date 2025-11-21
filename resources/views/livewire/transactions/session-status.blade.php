<div class="flex flex-col gap-2 p-5">

    <b>Hi, Jundey!</b>
    @if(!$activeSession && !$alreadyOpenedToday)
        <flux:button href="/transactions/create" variant="primary" size="sm" class="justify-start">
            <i class="fas fa-cash-register mr-2"></i> Open Register
        </flux:button>
    @elseif($alreadyOpenedToday && !$activeSession)
        <div class="px-2 py-1 rounded bg-red-50 dark:bg-red-900/30 text-xs text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700">
            You have already opened a session today. You cannot open another one.
        </div>
    @elseif(!$alreadyOpenedToday && $activeSession)
        <div class="px-2 py-1 rounded bg-red-50 dark:bg-red-900/30 text-xs text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700">
            You have an unclosed session. Please close the session first.
        </div>
        <flux:button variant="danger" href="/transactions/create" size="sm" class="cursor-pointer justify-start">
            <i class="fas fa-cash-register mr-2"></i> Close Previous Session
        </flux:button>
    @endif
    @if($activeSession && !$activeSession->closed_at && $alreadyOpenedToday)
        <div class="text-right text-xs">Total Collection:</div>
        <div style="font-family: 'DotMatrix', monospace;" class="p-2 pt-4 text-right text-xl bg-zinc-900 text-white text-[40px] leading-tight">
            P 12,312.00
        </div>
        <flux:button href="/transactions/create" variant="primary" size="sm" class="justify-start cursor-pointer bg-green-600 hover:bg-green-800 text-white">
            <i class="fas fa-check mr-2"></i> Continue Cashiering Session
        </flux:button>
        <flux:button
            variant="outline"
            size="sm"
            target="_blank"
            href="{{ route('reports.daily', ['date' => now()->format('Y-m-d')]) }}"
            class="justify-start cursor-pointer"
        >
            <i class="fas fa-print mr-2"></i> Preview Report
        </flux:button>
        <flux:button href="/transactions/create" variant="danger" size="sm" class="justify-start cursor-pointer">
            <i class="fas fa-lock mr-2"></i> Close Register
        </flux:button>
    @endif
</div>
