<div class="max-w-xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i class="fas fa-file-invoice text-zinc-600 dark:text-zinc-300"></i>
        Reports
    </h1>

    <div class="p-6 rounded-xl border border-zinc-200 dark:border-zinc-700
                bg-white dark:bg-zinc-900 space-y-4">

        <div class="space-y-4">

            {{-- Select Report Date --}}
            <flux:label>Date</flux:label>
            <flux:input type="date" wire:model="date" />

            {{-- Select Cashier/User --}}
            @if($canSelectUser)
                <div>
                    <flux:label>Cashier</flux:label>
                    <flux:select wire:model="userId">
                        @foreach($officeUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button wire:click="generate">
                    <i class="fas fa-print mr-1"></i> Generate
                </flux:button>
            </div>

        </div>
    </div>
</div>
