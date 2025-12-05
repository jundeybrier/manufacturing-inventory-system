<div>
    <button
        wire:click="syncNow"
        wire:loading.attr="disabled"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
    >
        @if ($isSyncing)
            <i class="fas fa-spinner fa-spin"></i> Syncing…
        @else
            <i class="fas fa-cloud-upload-alt"></i> Sync to Server
        @endif
    </button>
    <p class="text-sm text-gray-600 mt-2">
        Unsynced sessions: <strong>{{ $this->unsynced }}</strong>
    </p>
</div>
