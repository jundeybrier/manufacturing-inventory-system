<div class="p-4 space-y-4">
    <h2 class="text-lg font-semibold">Cash Breakdown</h2>

    @foreach($denominations as $index => $value)
        <div class="flex justify-between items-center">
            <label class="text-sm">₱{{ number_format($value) }}</label>
            <input type="number" min="0" wire:model.defer="counts.{{ $index }}" class="w-24 px-2 py-1 border rounded text-sm">
        </div>
    @endforeach

    <div class="flex justify-end mt-4">
        <flux:button wire:click="save" variant="primary" size="sm">
            Submit & Close Register
        </flux:button>
    </div>
</div>
