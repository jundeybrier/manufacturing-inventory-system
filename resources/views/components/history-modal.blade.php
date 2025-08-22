<x-modal-slide-over wire:model="{{ $show }}">
    <div class="p-4">
        <h2 class="text-lg font-semibold mb-4">
            <i class="fas fa-history mr-2"></i> Today's Collection History
        </h2>

        @if($transactions && count($transactions))
            <div class="space-y-3">
                @foreach($transactions as $transaction)
                    <div class="p-2 border-b">
                        <div class="font-medium">
                            {{ $transaction->or_number }} - ₱{{ number_format($transaction->amount_paid, 2) }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $transaction->fullname }} <br>
                            {{ $transaction->created_at->format('h:i A') }}
                        </div>
                        <ul class="text-xs text-gray-600 mt-1">
                            @foreach($transaction->details as $detail)
                                <li>{{ $detail->quantity }} × {{ $detail->name }} @ ₱{{ number_format($detail->price, 2) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-gray-500">No collections recorded today.</div>
        @endif

        <div class="mt-4 text-right">
            <flux:button variant="ghost" wire:click="$set('showHistoryModal', false)">Close</flux:button>
        </div>
    </div>
</x-modal-slide-over>
