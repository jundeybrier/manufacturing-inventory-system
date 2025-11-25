<div class="p-6 space-y-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            Deposits
        </h2>

        <flux:button wire:click="$set('showForm', true)" class="px-4">
            + New Deposit
        </flux:button>
    </div>

    {{-- Search Bar --}}
    <div class="flex items-center justify-between gap-3">
        <div class="mb-4 flex items-center gap-4">
            <flux:input
                name="search"
                wire:model.lazy="search"
                placeholder="Search deposits..."
                class="w-full max-w-xs"
            />
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-xs uppercase font-semibold tracking-wide
                       text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900/60">

                <th class="px-5 py-3 text-left">Date</th>
                <th class="px-5 py-3 text-left">Fund Source</th>
                <th class="px-5 py-3 text-right">Amount</th>
                <th class="px-5 py-3 text-left">Reference #</th>

                {{-- NEW: Office --}}
                <th class="px-5 py-3 text-left">Office</th>

                {{-- NEW: Created By --}}
                <th class="px-5 py-3 text-left">Created By</th>

            </tr>
            </thead>

            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @foreach ($deposits as $deposit)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">

                    <td class="px-5 py-3">
                        {{ \Carbon\Carbon::parse($deposit->date)->format('M d, Y') }}
                    </td>

                    <td class="px-5 py-3">
                        {{ $deposit->fundSource->name ?? '-' }}
                    </td>

                    <td class="px-5 py-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">
                        ₱ {{ number_format($deposit->amount, 2) }}
                    </td>

                    <td class="px-5 py-3">
                        {{ $deposit->reference_number ?: '—' }}
                    </td>

                    {{-- NEW: Office --}}
                    <td class="px-5 py-3">
                        {{ $deposit->office->name ?? '—' }}
                    </td>

                    {{-- NEW: Creator --}}
                    <td class="px-5 py-3">
                        {{ $deposit->user->name ?? '—' }}
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Slide-over Modal --}}
    <x-modal-slide-over wire:model="showForm">
        <div class="space-y-4">

            <h3 class="text-lg font-bold">New Deposit</h3>

            <flux:input type="date" label="Deposit Date"
                        wire:model.defer="date" required />

            <flux:select label="Fund Source"
                         wire:model.defer="fund_source_id" required>
                <option value="">Select fund source...</option>
                @foreach ($fundSources as $fs)
                    <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                @endforeach
            </flux:select>

            <flux:input type="number" step="0.01" min="1"
                        label="Amount"
                        wire:model.defer="amount" required />

            <flux:input type="text"
                        label="Reference Number"
                        wire:model.defer="reference_number"
                        placeholder="Deposit slip #" />

            <div class="flex justify-end gap-2 mt-6">
                <flux:button variant="outline"
                             wire:click="$set('showForm', false)">
                    Cancel
                </flux:button>
                <flux:button wire:click="save">
                    Save
                </flux:button>
            </div>

        </div>
    </x-modal-slide-over>

</div>
