<div>
    @if (!$printerPath)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4">
            ⚠️ Printer path is not set or unreachable.
        </div>
    @endif

    <div class="grid grid-cols-12 gap-4 p-6">
        <!-- COLUMN 1: Quick Report + Actions -->
        <div class="col-span-12 md:col-span-2 space-y-4">
            <div class="bg-white shadow rounded p-4">
                <h2 class="font-bold text-lg mb-2">Today's Summary</h2>
                <p>Total Collections: ₱{{ number_format($this->totalCollections, 2) }}</p>
                <p>Transactions: {{ $this->transactionCount }}</p>
                <p>Voided: {{ $this->voidedCount }}</p>
            </div>

            <div class="p-4 flex flex-col space-y-2">
                @if(!$activeSession && !$alreadyOpenedToday)
                    <flux:button wire:click="openSession" variant="outline" size="sm" class="justify-start">
                        <i class="fas fa-cash-register mr-2"></i> Open Register
                    </flux:button>
                @elseif($alreadyOpenedToday && !$activeSession)
                    <p class="text-xs text-red-600 italic">
                        You have already opened a session today. You cannot open another one.
                    </p>
                @endif

                @if($activeSession && !$activeSession->closed_at)
                    <flux:button wire:click="confirmClose" variant="outline" size="sm" class="justify-start text-red-600">
                        <i class="fas fa-lock mr-2"></i> Close Register
                    </flux:button>
                    @if($txn)
                        <flux:button wire:click="revalidate({{ $txn->id }})" variant="outline" size="sm" class="justify-start">
                            <i class="fas fa-sync-alt mr-2"></i> Revalidate
                        </flux:button>
                    @endif
                @endif


            </div>



            <div class="flex items-center mt-4">
                <input type="checkbox" id="printReceipt" wire:model.defer="printReceipt" class="mr-2">
                <label for="printReceipt" class="text-sm text-gray-700 dark:text-gray-300">Print Receipt</label>
            </div>
        </div>

        <!-- COLUMN 2: Services + Input Fields -->
        <div class="col-span-12 md:col-span-6 space-y-4">
            <div class="bg-white shadow rounded p-4">
                <h2 class="font-bold text-lg mb-2">Available Services</h2>
                @if($activeSession)
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($products as $service)
                            <button wire:click="selectProduct({{ $service->id }})"
                                    class="bg-gray-100 cursor-pointer hover:bg-blue-100 border px-4 py-2 rounded text-left">
                                {{ $service['name'] }} <br>
                                <small class="text-sm text-gray-500">₱{{ number_format($service['price'], 2) }}</small>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-red-700">Please open the register first.</p>
                @endif
            </div>

            @if($activeSession)
            <div class="bg-white shadow rounded p-4 space-y-4">
                <!-- First row: First Name, Middle Name, Last Name -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input wire:model="firstname" class="w-full border p-2 rounded" placeholder="First Name" />
                        @error('firstname')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <input wire:model="middlename" class="w-full border p-2 rounded" placeholder="Middle Name" />
                    </div>

                    <div>
                        <input wire:model="lastname" class="w-full border p-2 rounded" placeholder="Last Name" />
                        @error('lastname')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Second row: Remarks and OR Number -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <textarea wire:model="remarks" class="w-full border p-2 rounded h-[90px]" placeholder="Remarks"></textarea>
                    </div>

                    <div>
                        <input wire:model="or_number" class="w-full border p-2 rounded" placeholder="OR Number" />
                        @error('or_number')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- COLUMN 3: Selected Services + Total -->
        <div class="col-span-12 md:col-span-4 space-y-4">
            <div class="bg-white shadow rounded p-4 min-h-[500px]">
                <h2 class="font-bold text-lg mb-2">Transaction Items</h2>
                <table class="w-full text-left border-t text-sm">
                    <thead>
                    <tr>
                        <th class="py-2">Service</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Total</th>
                        <th class="py-2 text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($selectedServices as $s)
                        <tr class="border-t">
                            <td class="py-2">{{ $s['name'] }}</td>
                            <td class="py-2 text-center">{{ $s['quantity'] }}</td>
                            <td class="py-2 text-right">
                                ₱{{ number_format($s['amount'] * $s['quantity'], 2) }}
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="removeSelectedService({{ $s['id'] }})"
                                        class="text-red-600 hover:underline">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">No services selected</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($activeSession)
            <div class="bg-white shadow rounded p-4">
                <h2 class="text-lg font-bold mb-2">Total: ₱{{ number_format($totalAmount, 2) }}</h2>
                <button wire:click="submitTransaction"
                        class="bg-green-600 text-white cursor-pointer hover:bg-green-700 px-4 py-2 w-full rounded text-lg">Tender / Pay</button>
                <flux:button onclick="location.reload()" class="mt-3 w-full cursor-pointer">
                    <i class="fas fa-rotate-right mr-2 text-sm"></i> Cancel
                </flux:button>
            </div>
            @endif
        </div>
    </div>

    @if($showCloseModal)
        <x-modal-slide-over wire:model="showCloseModal">
            @livewire('transactions.breakdown-form', ['sessionId' => optional($activeSession)->id], key('breakdown-form-' . optional($activeSession)->id))
        </x-modal-slide-over>
    @endif
</div>
