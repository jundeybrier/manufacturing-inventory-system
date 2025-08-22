<div class="p-4 space-y-4">
    <h2 class="text-lg font-semibold">Cash Breakdown</h2>

    @foreach($denominations as $index => $value)
        <div class="flex justify-between items-center">
            <label class="text-sm">₱{{ number_format($value) }}</label>
            <input type="number" min="0" wire:model="counts.{{ $index }}" class="w-24 px-2 py-1 border rounded text-sm" wire:change="computeDenominationsTotal">
        </div>
    @endforeach
    <div class="flex justify-between items-center px-4 py-2 border-b">
        <span class="font-semibold">Denomination Total</span>
        <span class="text-xl font-bold">
        ₱ {{ number_format($denominationsTotal, 2) }}
    </span>
    </div>
    @error('denomination_total')
    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    <div class="flex justify-end mt-4">
        <flux:button wire:click="finalizeClose" variant="danger" size="sm">
            Submit & Close Register
        </flux:button>
    </div>
</div>
