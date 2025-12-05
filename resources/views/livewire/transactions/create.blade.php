<div>
    <style>
        button:disabled,
        input:disabled,
        textarea:disabled,
        select:disabled {
            opacity: 0.55 !important;
            cursor: not-allowed !important;
            background-color: #e5e7eb !important; /* gray-200 */
            color: #6b7280 !important; /* gray-500 */
            pointer-events: none !important;
        }

        /* Dark mode */
        html.dark button:disabled,
        html.dark input:disabled,
        html.dark textarea:disabled,
        html.dark select:disabled {
            background-color: #3f3f46 !important; /* zinc-700 */
            color: #a1a1aa !important; /* zinc-400 */
        }
    </style>
{{--    @if (!$printerPath)--}}
{{--        <div class="flex items-center bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 px-4 py-3 rounded mb-4 gap-2">--}}
{{--            <i class="fas fa-exclamation-triangle"></i>--}}
{{--            <span>Printer path is not set or unreachable.</span>--}}
{{--        </div>--}}
    @if(!$alreadyOpenedToday && $activeSession)
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
                    <i class="fas fa-chart-pie text-green-600"></i>
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
                    <flux:button
                        variant="outline"
                        size="sm"
                        target="_blank"
                        href="{{ route('reports.daily', ['date' => now()->format('Y-m-d')]) }}"
                        class="justify-start cursor-pointer"
                    >
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
                    <i class="fas fa-box-open text-green-600"></i>
                    Available Products
                </h2>

                @if($activeSession && $alreadyOpenedToday)
                    @php
                        $hiddenProducts = auth()->user()->pref('products.hidden', []);
                    @endphp
                    <div class="flex justify-end mb-3">
                        <button
                            @disabled($jsonLocked)
                            wire:click="openManageProducts"
                            class="text-sm px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700
               bg-white dark:bg-zinc-900 hover:bg-gray-100 dark:hover:bg-zinc-800
               transition-colors"
                        >
                            <i class="fas fa-gear"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-2">

                        @foreach ($products as $product)
                            @php
                                $total = 0;
                                $hasVariable = false;

                                foreach ($product->services as $service) {
                                    foreach ($service->feeComponents as $fee) {
                                        if ($fee->is_active) {
                                            $fee->is_variable ? $hasVariable = true : $total += $fee->base_amount;
                                        }
                                    }
                                }
                            @endphp

                            <div class="relative group h-full flex">
                                {{-- Product selection button --}}
                                <button
                                    @disabled($jsonLocked)
                                    wire:click="selectProduct({{ $product->id }})"
                                    class="w-full h-full flex flex-col cursor-pointer text-left border border-gray-300 dark:border-gray-700
                       rounded-xl px-4 py-3 bg-gray-50 dark:bg-zinc-900/60 hover:shadow hover:border-blue-400
                       dark:hover:border-blue-400 transition-colors focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                >
                                    <div class="font-semibold text-gray-800 dark:text-gray-100 text-base">
                                        {{ $product->name }}
                                    </div>

                                    @if($product->description)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            {{ $product->description }}
                                        </div>
                                    @endif

                                    <div class="mt-auto pt-2 flex items-center gap-2">
                    <span class="font-bold text-lg text-green-700 dark:text-green-400">
                        ₱{{ number_format($total, 2) }}
                    </span>

                                        @if($hasVariable)
                                            <span class="ml-2 inline-block px-2 py-0.5 rounded-full text-xs border border-orange-400
                              text-orange-700 dark:border-orange-500 dark:text-orange-300 bg-orange-50
                              dark:bg-orange-900/40 font-semibold">
                            + variable
                        </span>
                                        @endif
                                    </div>

                                    @if($product->services->count())
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Includes: {{ $product->services->pluck('name')->implode(', ') }}
                                        </div>
                                    @endif
                                </button>
                            </div>
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
                                <input @disabled($jsonLocked) autofocus wire:model="reference" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Reference" />
                                @error('reference')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input @disabled($jsonLocked) wire:model="firstname" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="First Name" />
                                @error('firstname')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <input @disabled($jsonLocked) wire:model="middlename" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Middle Name" />
                            </div>
                            <div>
                                <input @disabled($jsonLocked) wire:model="lastname" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100 p-2 rounded focus:ring-2 focus:ring-blue-400" placeholder="Last Name" />
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
                    @forelse ($selectedServices as $s)
                        @php
                            // Determine rate
                            $rate = ($s['currency'] ?? 'PHP') === 'USD'
                                ? ($usdConversionRate ?? 1)
                                : 1;

                            // Compute total in both currencies
                            $baseTotal = floatval($s['amount']) * intval($s['quantity']);
                            $displayTotalPhp = $baseTotal * $rate;
                        @endphp

                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="py-2">
                                <div class="font-semibold">{{ $s['service_name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $s['product_name'] }}</div>

                                @if(($s['currency'] ?? 'PHP') === 'USD')
                                    <div class="text-xs text-blue-500">
                                        ${{ number_format($baseTotal, 2) }} × {{ number_format($rate, 2) }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-2 text-center">
                                <input type="number" min="1"
                                       @disabled($jsonLocked)
                                       wire:model.defer="selectedServices.{{ $loop->index }}.quantity"
                                       class="w-12 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-center bg-white dark:bg-zinc-800/50 text-gray-900 dark:text-gray-100"
                                       wire:change="recalculateTotal" />
                            </td>

                            <td class="py-2 text-right">
                                ₱{{ number_format($displayTotalPhp, 2) }}
                            </td>

                            <td class="py-2 text-right">
                                <button @disabled($jsonLocked) wire:click="removeSelectedService({{ $s['service_id'] }})"
                                        class="text-red-600 hover:underline">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">No services selected</td>
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
                    @if($modalProduct && $modalProduct->services)
                        @foreach ($modalProduct->services as $service)
                            <div class="mb-6 pb-4 border-b border-gray-300 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-200 mb-4">
                                    {{ $service->name }}
                                </h4>

                                @foreach ($service->feeComponents->where('is_variable', true) as $fee)
                                    <div class="mb-4">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            {{ $fee->name }}
                                            @if($fee->currency === 'USD')
                                                <span class="ml-1 text-blue-600 dark:text-blue-400">(USD Amount)</span>
                                            @endif
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            wire:model.defer="variableAmounts.{{ $fee->id }}"
                                            placeholder="Enter amount"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2
                                   bg-white dark:bg-zinc-800/70 text-gray-900 dark:text-gray-100
                                   focus:outline-none focus:ring-2 focus:ring-blue-400/70"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        {{-- USD Exchange Rate --}}
                        @if(collect($modalProduct->services)->flatMap->feeComponents->where('currency', 'USD')->count())
                            <div class="mb-5">
                                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                                    USD Conversion Rate
                                </label>

                                @if($isExchangeRateLocked)
                                    <input type="text"
                                           class="w-full border border-gray-300 dark:border-gray-600
                                  rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-700
                                  text-gray-700 dark:text-gray-200"
                                           value="{{ $usdConversionRate }}"
                                           readonly />

                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        This conversion rate is locked for today ({{ now()->format('M d, Y') }}).
                                    </p>
                                @else
                                    <input type="number"
                                           wire:model.defer="usdConversionRate"
                                           step="0.01"
                                           placeholder="Enter today's rate"
                                           class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2
                                  bg-white dark:bg-zinc-800/70 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-400/70"
                                    />

                                    @error('usdConversionRate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex justify-end mt-6 space-x-2">
                        <flux:button wire:click="applyVariableFees">
                            <i class="fas fa-save mr-1"></i> Confirm
                        </flux:button>

                        <flux:button variant="ghost" wire:click="$set('showVariableModal', false)">
                            Cancel
                        </flux:button>
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
                                        @php
                                            // ✅ Group details by service
                                            $serviceGroups = $transaction->details
                                                ->groupBy('service_id')
                                                ->map(function($items) {
                                                    return [
                                                        'service_name' => $items->first()->service->name ?? 'Unknown Service',
                                                        'total' => $items->sum('total'),
                                                    ];
                                                });
                                        @endphp

                                        <tr>
                                            <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 whitespace-nowrap align-top">
                                                {{ \Carbon\Carbon::parse($transaction->created_at ?? $transaction->datetime_created)->format('h:i A') }}
                                            </td>

                                            <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 align-top">
                                                <div class="font-semibold">{{ $transaction->or_number }}</div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $transaction->customer_name
                                                        ?? trim(($transaction->firstname ?? '') . ' ' . ($transaction->middlename ?? '') . ' ' . ($transaction->lastname ?? ''))
                                                        ?? '-' }}
                                                </div>
                                            </td>

                                            <td class="p-2 border border-gray-300 dark:border-gray-600 text-green-700 dark:text-green-400 font-bold text-right align-top">
                                                @php
                                                    $details = $transaction->details()->get(); // ensures fresh relationship load

                                                    $hasUsd = $details->contains('currency', 'USD')
                                                        || $details->contains(fn($d) => !is_null($d->exchange_rate));

                                                    $symbol = $hasUsd ? '$' : '₱';
                                                @endphp

                                                {{ $symbol }}{{ number_format($transaction->computedTotal, 2) }}

                                                {{-- 💡 Now show services instead of individual fees --}}
                                                <ul class="text-xs text-gray-400 text-left mt-1 ml-1">
                                                    @foreach($serviceGroups as $service)
                                                        <li>
                                                            <span class="font-medium">{{ $service['service_name'] }}</span> —
                                                            ₱{{ number_format($service['total'], 2) }}
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                {{-- Void button --}}
                                                @if(!$transaction->is_voided)
                                                    <flux:button size="sm" tone="danger" class="mt-2"
                                                                 wire:click="confirmVoid({{ $transaction->id }})">
                                                        <i class="fas fa-ban mr-1"></i> Void
                                                    </flux:button>
                                                @else
                                                    <div class="mt-2 text-xs text-red-500 font-semibold">(Voided)</div>
                                                @endif
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
            @if($showVoidModal)
                <x-modal wire:model="showVoidModal">
                    <div class="p-6 space-y-4">
                        <h2 class="text-lg font-bold flex items-center">
                            <i class="fas fa-ban text-red-500 mr-2"></i> Void Transaction
                        </h2>

                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            Are you sure you want to void OR #{{ $selectedTransaction?->or_number }}?
                        </div>

                        <flux:textarea
                            wire:model.defer="void_reason"
                            label="Explanation / Reason"
                            placeholder="Enter reason for voiding this transaction..."
                            rows="3"
                        />

                        @error('void_reason')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-2 mt-4">
                            <flux:button variant="ghost" wire:click="$set('showVoidModal', false)">Cancel</flux:button>
                            <flux:button tone="danger" wire:click="voidTransaction">Confirm Void</flux:button>
                        </div>
                    </div>
                </x-modal>
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
        @if($showManageProducts)
            <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
                <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl p-6 w-[420px] max-h-[80vh] flex flex-col overflow-hidden">

                    <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-500"></i>
                        Manage Products
                    </h3>

                    {{-- Product List --}}
                    <div class="space-y-2 overflow-y-auto pr-1 border-t border-gray-200 dark:border-gray-700 pt-3">
                        @foreach(\App\Models\Product::orderBy('name')->get() as $product)
                            @php
                                $isHidden = in_array($product->id, $hiddenProducts);
                            @endphp

                            <div class="flex justify-between items-center py-1.5 px-2 bg-gray-50 dark:bg-zinc-800/40 rounded-lg">
                    <span class="text-sm {{ $isHidden ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-gray-200' }}">
                        {{ $product->name }}
                    </span>

                                <button
                                    wire:click="toggleProductVisibility({{ $product->id }})"
                                    class="text-xs font-semibold px-3 py-1 rounded-lg transition
                            {{ $isHidden
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900'
                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900' }}"
                                >
                                    {{ $isHidden ? 'Unhide' : 'Hide' }}
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end mt-5 border-t border-gray-200 dark:border-gray-700 pt-3">
                        <button
                            wire:click="closeManageProducts"
                            class="text-sm px-4 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600
                bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 transition"
                        >
                            Close
                        </button>
                    </div>

                </div>
            </div>
        @endif

        @if($showJsonModal)
            <div
                x-data
                x-init="$nextTick(() => $refs.jsonField.focus())"
                class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center"
                style="backdrop-filter: blur(2px)"
            >
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl p-6 w-full max-w-xl border">

                    <h2 class="text-lg font-bold mb-3 text-blue-700 dark:text-blue-300 flex items-center">
                        <i class="fas fa-code mr-2"></i> JSON Input
                    </h2>

                    <textarea
                        x-ref="jsonField"
                        wire:model.defer="jsonInput"
                        placeholder='{"id":"AUTH-189154",...}'
                        class="w-full h-52 p-3 border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-200 rounded-lg
                       focus:ring-2 focus:ring-blue-400"
                        @keydown.enter.prevent="$wire.processJson()"
                    ></textarea>

                    @error('jsonInput')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-2 mt-4">
                        <flux:button variant="ghost" wire:click="$set('showJsonModal', false)">Cancel</flux:button>
                        <flux:button wire:click="processJson">
                            <i class="fas fa-check mr-1"></i> Process
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

    </div>
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('print-receipt', (data) => {
                    const id = data.transactionId;
                    const isRevalidate = data.revalidate ?? false;
                    const url = `/print/receipt/${id}` + (isRevalidate ? '?mode=revalidate' : '');
                    const w = window.open(url, '_blank', 'width=400,height=600');
                    if (!w) alert('Please allow popups to print receipts.');
                });
            });

                document.addEventListener('keydown', function (e) {
                // CTRL + SHIFT + J
                if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                Livewire.dispatch('open-json-modal');
            }
            });
        </script>
</div>
