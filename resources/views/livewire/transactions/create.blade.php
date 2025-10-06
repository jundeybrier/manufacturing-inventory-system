<div>
    @if (!$printerPath)
        <div class="flex items-center bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 px-4 py-3 rounded mb-4 gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Printer path is not set or unreachable.</span>
        </div>
    @elseif(!$alreadyOpenedToday && $activeSession)
        <div class="flex items-center bg-red-50 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 rounded mb-4 gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <span>You have an unclosed session ({{ \Carbon\Carbon::parse($activeSession->opened_at)->format('F d, Y') }}). Please close the session first.</span>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6 p-6">
        <!-- COLUMN 1: Quick Report + Actions -->
        <div class="col-span-12 md:col-span-2 space-y-4">
            <div class="border border-gray-200 dark:border-zinc-700 shadow rounded-xl p-5 bg-white dark:bg-zinc-900/80">
                <h2 class="font-bold text-lg mb-2 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-blue-500"></i>
                    Today's Summary
                </h2>
                <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                        <span>Total Collections</span>
                        <span class="font-semibold text-green-700 dark:text-green-400">₱{{ $activeSession?number_format($activeSession->totalCollections, 2):0 }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Transactions</span>
                        <span>{{ $activeSession?$activeSession->transactionCount:0 }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Voided</span>
                        <span>{{ $activeSession?$activeSession->voidedCount:0 }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                @if(!$activeSession && !$alreadyOpenedToday)
                    <flux:button wire:click="openSession" variant="outline" size="sm" class="justify-start">
                        <i class="fas fa-cash-register mr-2"></i> Open Register
                    </flux:button>
                @elseif($alreadyOpenedToday && !$activeSession)
                    <div class="px-2 py-1 rounded bg-red-50 dark:bg-red-900/30 text-xs text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700">
                        You have already opened a session today. You cannot open another one.
                    </div>
                @elseif(!$alreadyOpenedToday && $activeSession)
                    <div class="px-2 py-1 rounded bg-red-50 dark:bg-red-900/30 text-xs text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700">
                        You have an unclosed session. Please close the session first.
                    </div>
                    <flux:button variant="danger" wire:click="confirmClose" size="sm" class="cursor-pointer justify-start">
                        <i class="fas fa-cash-register mr-2"></i> Close Previous Session
                    </flux:button>
                    <flux:button wire:click="showHistory" variant="outline" size="sm" class="justify-start cursor-pointer">
                        <i class="fas fa-history mr-2"></i> History
                    </flux:button>
                @endif
                @if($activeSession && !$activeSession->closed_at && $alreadyOpenedToday)
                    <flux:button wire:click="showHistory" variant="outline" size="sm" class="justify-start cursor-pointer">
                        <i class="fas fa-history mr-2"></i> History
                    </flux:button>
                    <flux:button variant="outline" size="sm" target="_blank" href="{{ route('transactions.daily-report') }}" class="justify-start cursor-pointer">
                        <i class="fas fa-print mr-2"></i> Preview Report
                    </flux:button>
                    <flux:button wire:click="confirmClose" variant="outline" size="sm" class="justify-start cursor-pointer">
                        <i class="fas fa-lock mr-2"></i> Close Register
                    </flux:button>
                    @if($txn)
                        <flux:button wire:click="revalidate({{ $txn->id }})" variant="outline" size="sm" class="justify-start cursor-pointer">
                            <i class="fas fa-sync-alt mr-2"></i> Revalidate
                        </flux:button>
                    @endif
                @endif
            </div>
            <div class="flex items-center mt-6">
                <input type="checkbox" id="printReceipt" wire:model.defer="printReceipt" class="mr-2 accent-blue-500">
                <label for="printReceipt" class="text-sm text-gray-700 dark:text-gray-300">Print Receipt</label>
            </div>
        </div>

        <!-- COLUMN 2: Services + Input Fields -->
        <div class="col-span-12 md:col-span-6 space-y-4">
            <div class="border border-gray-200 dark:border-zinc-700 shadow rounded-xl p-5 bg-white dark:bg-zinc-900/80">
                <h2 class="font-bold text-lg mb-3 flex items-center gap-2">
                    <i class="fas fa-cogs text-indigo-400"></i>
                    Available Services
                </h2>
                @if($activeSession && $alreadyOpenedToday)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-2">
                        @foreach ($services as $service)
                            @php
                                $fixedTotal = $service->feeComponents
                                    ->where('is_active', true)
                                    ->where('is_variable', false)
                                    ->sum('base_amount');
                                $hasVariable = $service->feeComponents
                                    ->where('is_active', true)
                                    ->where('is_variable', true)
                                    ->count() > 0;
                            @endphp
                            <button
                                wire:click="selectService({{ $service->id }})"
                                class="w-full cursor-pointer text-left border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 bg-gray-50 dark:bg-zinc-900/60 hover:shadow hover:border-blue-400 dark:hover:border-blue-400 transition-colors focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                aria-label="Select {{ $service['name'] }}"
                            >
                                <div class="font-semibold text-gray-800 dark:text-gray-100 text-base">
                                    {{ $service['name'] }}
                                </div>
                                @if($service->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 truncate">
                                        {{ $service->description }}
                                    </div>
                                @endif
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="font-bold text-lg text-green-700 dark:text-green-400">
                                        ₱{{ number_format($fixedTotal, 2) }}
                                    </span>
                                    @if($hasVariable)
                                        <span class="ml-2 inline-block px-2 py-0.5 rounded-full text-xs border border-orange-400 text-orange-700 dark:border-orange-500 dark:text-orange-300 bg-orange-50 dark:bg-orange-900/40 font-semibold">
                                            + variable fee
                                        </span>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="px-3 py-2 rounded bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-700">
                        Please open the register first.
                    </div>
                @endif

                @if($activeSession && $alreadyOpenedToday)
                    <div x-data x-init="$nextTick(() => $refs.reference.focus())" class="border border-gray-200 dark:border-zinc-700 shadow rounded-xl p-4 space-y-4 mt-4 bg-white dark:bg-zinc-900/80">
                        <div class="grid grid-cols-1 md:grid-cols-1">
                            <div>
                                <input autofocus wire:model="reference" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Reference" />
                                @error('reference')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input wire:model="firstname" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="First Name" />
                                @error('firstname')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <input wire:model="middlename" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Middle Name" />
                            </div>
                            <div>
                                <input wire:model="lastname" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Last Name" />
                                @error('lastname')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <textarea wire:model="remarks" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded h-[90px] focus:ring-2 focus:ring-blue-400" placeholder="Remarks"></textarea>
                            </div>
                            <div>
                                <input wire:model="or_number" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="OR Number" />
                                @error('or_number')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- COLUMN 3: Selected Fees + Total -->
        <div class="col-span-12 md:col-span-4 space-y-4">
            <div class="border border-gray-200 dark:border-zinc-700 shadow rounded-xl p-5 bg-white dark:bg-zinc-900/80 min-h-[500px]">
                <h2 class="font-bold text-lg mb-2 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-emerald-500"></i>
                    Transaction Items
                </h2>
                <table class="w-full text-left border-t text-sm">
                    <thead>
                    <tr class="text-gray-600 dark:text-gray-300">
                        <th class="py-2">Fee</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Amount</th>
                        <th class="py-2 text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($selectedFees as $f)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="py-2">
                                <div class="font-semibold">{{ $f['fee_name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $f['service_name'] }}</div>
                            </td>
                            <td class="py-2 text-center">
                                <input type="number" min="1"
                                       wire:model.defer="selectedFees.{{ $loop->index }}.quantity"
                                       class="w-12 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-center bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100"
                                       wire:change="recalculateTotal" />
                            </td>
                            <td class="py-2 text-right">
                                {{ $f['currency'] }}{{ number_format($f['price'] * $f['quantity'], 2) }}
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="removeSelectedFee({{ $f['fee_id'] }})"
                                        class="text-red-600 hover:underline">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">No fees selected</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
            @error('session')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @if($activeSession)
                <div class="border border-gray-200 dark:border-zinc-700 shadow rounded-xl p-5 bg-white dark:bg-zinc-900/80">
                    <h2 class="text-lg font-bold mb-2 flex items-center gap-2">
                        <i class="fas fa-calculator text-green-600"></i>
                        Total: <span class="ml-2 text-green-700 dark:text-green-400">₱{{ number_format($totalAmount, 2) }}</span>
                    </h2>
                    <button wire:click="confirmSubmit"
                            class="bg-green-600 dark:bg-green-700 text-white cursor-pointer hover:bg-green-700 dark:hover:bg-green-800 px-4 py-2 w-full rounded-lg text-lg font-semibold transition">
                        <i class="fas fa-check-circle mr-2"></i> Tender / Pay
                    </button>
                    <flux:button onclick="location.reload()" class="mt-3 w-full cursor-pointer">
                        <i class="fas fa-rotate-right mr-2 text-sm"></i> Cancel
                    </flux:button>
                </div>
            @endif


{{--            VARIABLE AMOUNT--}}
            <x-modal-slide-over wire:model="showVariableModal" title="Set Fee Amounts">
                <div>
                    @if($modalService && $modalService->feeComponents)
                        @foreach ($modalService->feeComponents as $fee)
                            <div class="mb-3">
                                <label class="block text-sm font-medium mb-1">
                                    {{ $fee->name }}
                                    @if($fee->currency === 'USD')
                                        <span class="ml-2 text-xs text-blue-500">(in $)</span>
                                    @endif
                                </label>
                                <input type="number" min="0" step="0.01"
                                       wire:model.defer="variableAmounts.{{ $fee->id }}"
                                       placeholder="{{ $fee->is_variable ? 'Enter amount' : number_format($fee->base_amount,2) }}"
                                       class="w-full border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100"
                                       @if(!$fee->is_variable && $fee->currency !== 'USD') disabled @endif
                                />
                            </div>
                        @endforeach
                        @if($modalService->feeComponents->where('currency', 'USD')->count())
                            <div class="mb-3">
                                <label class="block text-sm font-medium">USD Exchange Rate</label>

                                @if($isExchangeRateLocked)
                                    <input type="text"
                                           class="input input-bordered w-full bg-gray-100 dark:bg-gray-700"
                                           value="{{ $usdConversionRate }}"
                                           readonly />
                                    <p class="text-xs text-gray-500 mt-1">Locked for today ({{ now()->format('M d, Y') }})</p>
                                @else
                                    <input type="number"
                                           wire:model.defer="usdConversionRate"
                                           step="0.0001"
                                           class="input input-bordered w-full"
                                           placeholder="Enter today's rate" />
                                @endif
                            </div>
                        @endif
                    @endif

                    <div class="flex justify-end mt-4">
                        <flux:button wire:click="applyVariableFees" class="mr-2"><i class="fas fa-save mr-1"></i> OK</flux:button>
                        <flux:button variant="ghost" wire:click="$set('showVariableModal', false)">Cancel</flux:button>
                    </div>
                </div>
            </x-modal-slide-over>

            <x-modal-slide-over wire:model="showCloseModal" title="Set Fee Amounts">
                @include('livewire.transactions.breakdown-form')
            </x-modal-slide-over>

            @if($showHistoryModal)
                <x-modal-slide-over wire:model="showHistoryModal">
                    <div class="p-4">
                        <h2 class="text-lg font-bold mb-4 flex items-center">
                            <i class="fas fa-history mr-2"></i> Collection History
                        </h2>

                        @if($todayHistory && count($todayHistory))
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
                                    <thead>
                                    <tr>
                                        <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Time</th>
                                        <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Customer</th>
                                        <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($todayHistory as $transaction)
                                        <tr>
                                            <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($transaction->created_at ?? $transaction->datetime_created)->format('h:i A') }}
                                            </td>
                                            <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                                <div class="font-semibold">{{ $transaction->or_number }}</div>
                                                <div class="text-xs text-gray-400">{{ $transaction->customer_name
                                                    ?? trim(($transaction->firstname ?? '') . ' ' . ($transaction->middlename ?? '') . ' ' . ($transaction->lastname ?? ''))
                                                    ?? '-' }}</div>

                                            </td>
                                            <td class="p-2 border border-gray-300 dark:border-gray-600 text-green-700 dark:text-green-400 font-bold text-right">
                                                ₱{{ number_format($transaction->computedTotal, 2) }}
                                                <div class="text-xs text-gray-400">
                                                    @foreach($transaction->details as $detail)
                                                        <li>
                                                            <span class="font-medium">{{ $detail->quantity }} × {{ $detail->description ?? $detail->name }}</span>
                                                            @ {{ $detail->currency ?? 'PHP' }}{{ number_format($detail->amount, 2) }}
                                                        </li>
                                                        @if($detail->currency === 'USD')
                                                            <li>1 USD = PHP {{ number_format($detail->exchange_rate,2)}}</li>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-gray-500">No collections recorded today.</div>
                        @endif

                        <div class="mt-4 text-right">
                            <flux:button variant="ghost" wire:click="$set('showHistoryModal', false)">Close</flux:button>
                        </div>
                    </div>
                </x-modal-slide-over>
            @endif
            @if($showConfirmModal)
                <div
                    x-data
                    x-init="$nextTick(() => $refs.confirmBtn.focus())"
                    class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center"
                    style="backdrop-filter: blur(2px)"
                >
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl p-8 w-full max-w-md border z-50 animate-fade-in">
                        <div class="text-xl font-bold mb-2 text-blue-700 dark:text-blue-300 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Confirm Submission
                        </div>
                        <div class="mb-6 text-zinc-700 dark:text-zinc-300">
                            Are you sure you want to submit this transaction? This action cannot be undone.
                        </div>
                        <form wire:submit.prevent="submitTransaction" class="flex gap-4 justify-end">
                            <button type="button" wire:click="$set('showConfirmModal', false)"
                                    class="px-4 py-2 rounded-lg border border-zinc-400 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                    x-ref="confirmBtn"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow transition">
                                Confirm & Submit
                            </button>
                        </form>
                    </div>
                </div>
            @endif



        </div>
    </div>
</div>
