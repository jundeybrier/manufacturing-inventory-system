<div class="p-6 space-y-4">
    <div class="max-h-96 overflow-auto text-left bg-gray-100 dark:bg-gray-900 rounded-lg p-4 mb-6 text-sm text-gray-800 dark:text-gray-100 border border-gray-200 dark:border-gray-700">
    <pre class="whitespace-pre-wrap break-all">
{{ json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
    </pre>
    </div>

    <button wire:click="syncNow"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-cloud-upload-alt mr-1"></i> Sync Now
    </button>
</div>
