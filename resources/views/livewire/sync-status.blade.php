<div class="p-6 space-y-6">

    {{-- Live Log Console --}}
    <div
        x-data="{
            log: @entangle('log').live,
            scroll() { $refs.box.scrollTop = $refs.box.scrollHeight }
        }"
        x-init="$watch('log', () => scroll())"
        class="bg-gray-900 text-green-400 font-mono text-xs p-4 rounded border border-gray-700 shadow-inner h-64 overflow-y-auto"
        x-ref="box"
    >
        @forelse($log as $line)
            <div>{{ $line }}</div>
        @empty
            <div class="text-gray-500 italic">No recent sync actions yet…</div>
        @endforelse
    </div>

    {{-- Buttons --}}
    <div class="flex items-center justify-center gap-4 mt-6">

        {{-- Sync Button --}}
        <flux:button
            variant="primary"
            wire:click="syncNow"
            :disabled="$isSyncing"
            spinner
        >
            <i class="fas fa-cloud-upload-alt mr-1"></i> Sync Now
        </flux:button>

        {{-- Login Redirect --}}
        <flux:button
            variant="outline"
            href="{{ route('login') }}"
        >
            Go to Login
        </flux:button>
    </div>
</div>
