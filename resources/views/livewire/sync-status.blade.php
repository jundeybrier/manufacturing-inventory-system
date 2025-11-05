<div class="p-6 space-y-6 text-center">
    {{-- Status Box --}}
    <div
        class="max-h-96 overflow-auto text-left bg-gray-100 dark:bg-gray-900 rounded-lg p-4 text-sm
               text-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700 shadow-inner">
        <pre class="whitespace-pre-wrap break-all">
{{ json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
        </pre>
    </div>

    {{-- Buttons --}}
    <div class="flex items-center justify-center gap-4">
        {{-- Sync Button --}}
        <flux:button
            variant="primary"
            wire:click="syncNow"
            spinner
        >
            <i class="fas fa-cloud-upload-alt mr-1"></i> Sync Now
        </flux:button>

        {{-- Login Redirect Button --}}
        <flux:button
            variant="outline"
            href="{{ route('login') }}"
        >
            Go to Login
        </flux:button>
    </div>
</div>
