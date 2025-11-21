<div class="mx-auto max-w-3xl w-full space-y-5">

    @foreach($preferences as $key => $data)
        <div class="p-4 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm">

            <div class="flex items-center gap-2 mb-3">
                @php
                    $icon = match($key) {
                        default => 'fa-user-tie'
                    };
                @endphp

                <i class="fa-solid {{ $icon }} text-green-500"></i>
                <h4 class="text-sm font-bold tracking-wide text-gray-800 dark:text-gray-200 uppercase">
                    {{ str_replace('_', ' ', $key) }}
                </h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <flux:input
                    label="Full Name"
                    wire:model.defer="preferences.{{ $key }}.name"
                    placeholder="Juan Dela Cruz"
                    class="text-sm"
                />

                <flux:input
                    label="Designation / Title"
                    wire:model.defer="preferences.{{ $key }}.designation"
                    placeholder="e.g. Supervising Consular Officer"
                    class="text-sm"
                />
            </div>

        </div>
    @endforeach

    <div class="pt-3 text-right">
        <flux:button wire:click="save" class="px-6 font-semibold">
            <i class="fas fa-save mr-2"></i> Save Preferences
        </flux:button>
    </div>

</div>
