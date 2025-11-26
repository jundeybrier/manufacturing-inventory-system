<div class="space-y-8">

    {{-- HEADER --}}
    <div>
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Receipt Layout & Formatting
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Customize the layout, spacing, and font size of your Form 51 receipts.
        </p>
    </div>

    {{-- RECEIPT TYPE --}}
    <div class="p-4 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm">
        <flux:select
            label="Receipt Type"
            wire:model.live="layout.receipt_type"
        >
            <option value="continuous_form51">Form 51 – Continuous</option>
            <option value="booklet_form51">Form 51 – Booklet</option>
        </flux:select>
    </div>

    {{-- SETTINGS + PREVIEW GRID --}}
    <div class="grid grid-cols-1 xl:grid-cols-[30%_70%] gap-6">

        {{-- LEFT: SETTINGS --}}
        <div class="space-y-6">

            {{-- Component Editor Group --}}
            @php
                $groups = [
                    'office_name'   => 'Office Name',
                    'date'          => 'Date/Time',
                    'payor_info'    => 'Payor / OR# / Reference',
                    'particulars'   => 'Particulars (List)',
                    'total'         => 'Total Amount',
                    'amount_words'  => 'Amount in Words',
                    'cashier_name'  => 'Name of Collecting Officer',
                ];
            @endphp

            @foreach($groups as $fieldKey => $label)
                <div class="p-4 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-align-left text-primary-500"></i>
                        <h4 class="text-sm font-bold uppercase text-gray-800 dark:text-gray-300">
                            {{ $label }}
                        </h4>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <flux:input
                            type="number"
                            label="Font Size"
                            wire:model.live="layout.{{ $fieldKey }}.font"
                        />

                        <flux:input
                            type="number"
                            label="X (mm)"
                            wire:model.live="layout.{{ $fieldKey }}.x"
                        />

                        <flux:input
                            type="number"
                            label="Y (mm)"
                            wire:model.live="layout.{{ $fieldKey }}.y"
                        />
                    </div>

                    @if($fieldKey === 'particulars')
                        <div class="mt-3 grid grid-cols-2 gap-3">

                            <flux:input
                                type="number"
                                label="Line Spacing (px)"
                                wire:model.live="layout.particulars.spacing"
                            />

                            <flux:input
                                type="number"
                                label="Amount X (mm)"
                                wire:model.live="layout.particulars.x_amount"
                            />
                        </div>
                    @endif
                </div>
            @endforeach

        </div>

        {{-- RIGHT: REAL-TIME PREVIEW --}}
        <div class="relative border dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-900 p-4 shadow">

            <div class="text-sm font-semibold mb-3 text-gray-700 dark:text-gray-300">
                Live Preview
            </div>

            <div class="relative mx-auto border dark:border-zinc-700 overflow-hidden"
                 style="
                    width: {{ $previewWidth }}px;
                    height: {{ $previewHeight }}px;
                    color: #000;        /* ← Force preview text color */
                    background-image: url('{{ $previewImage }}');
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                 ">

                {{-- Overlay Items --}}
                {{-- OFFICE NAME --}}
                <div class="absolute"
                     style="
                        top: {{ $layout['office_name']['y'] ?? 0 }}mm;
                        left: {{ $layout['office_name']['x'] ?? 0 }}mm;
                        font-size: {{ $layout['office_name']['font'] ?? 12 }}px;
                     ">
                    OFFICE OF CONSULAR AFFAIRS
                </div>

                {{-- DATE --}}
                <div class="absolute"
                     style="
                        top: {{ $layout['date']['y'] ?? 0 }}mm;
                        left: {{ $layout['date']['x'] ?? 0 }}mm;
                        font-size: {{ $layout['date']['font'] ?? 10 }}px;
                     ">
                    {{ now()->format('F d, Y H:i') }}
                </div>

                {{-- PAYOR INFO --}}
                <div class="absolute"
                     style="
                        top: {{ $layout['payor_info']['y'] ?? 0 }}mm;
                        left: {{ $layout['payor_info']['x'] ?? 0 }}mm;
                        font-size: {{ $layout['payor_info']['font'] ?? 10 }}px;
                     ">
                    Name: Juan Dela Cruz<br>
                    OR #: 1234567<br>
                    Ref: ABC-999 / Sample
                </div>

                {{-- PARTICULARS LIST --}}
                @php
                    $yStart = $layout['particulars']['y'] ?? 0;
                    $xDesc  = $layout['particulars']['x'] ?? 0;
                    $xAmt   = $layout['particulars']['x_amount'] ?? 0;
                    $spacing = $layout['particulars']['spacing'] ?? 12;

                    $items = [
                        ['desc' => 'Authentication', 'amt' => '₱100.00'],
                        ['desc' => 'Releasing',       'amt' => '₱50.00'],
                    ];
                @endphp

                {{-- DESCRIPTIONS --}}
                @foreach ($items as $i => $row)
                    <div class="absolute"
                         style="
                        top: {{ ($yStart + ($i * $spacing)) }}mm;
                        left: {{ $xDesc }}mm;
                        font-size: {{ $layout['particulars']['font'] ?? 10 }}px;
                     ">
                                    {{ $row['desc'] }}
                                </div>
                            @endforeach

                            {{-- AMOUNTS (Aligned Column) --}}
                            @foreach ($items as $i => $row)
                                <div class="absolute right-0"
                                     style="
                        top: {{ ($yStart + ($i * $spacing)) }}mm;
                        right: calc(100% - {{ $xAmt }}mm);
                        text-align: right;
                        font-size: {{ $layout['particulars']['font'] ?? 10 }}px;
                     ">
                                    {{ $row['amt'] }}
                                </div>
                @endforeach

                {{-- TOTAL --}}
                <div class="absolute"
                     style="
                        top: {{ $layout['total']['y'] ?? 0 }}mm;
                        left: {{ $layout['total']['x'] ?? 0 }}mm;
                        font-size: {{ $layout['total']['font'] ?? 12 }}px;
                     ">
                    ₱150.00
                </div>

                {{-- AMOUNT IN WORDS --}}
                <div class="absolute"
                     style="
                        top: {{ $layout['amount_words']['y'] ?? 0 }}mm;
                        left: {{ $layout['amount_words']['x'] ?? 0 }}mm;
                        font-size: {{ $layout['amount_words']['font'] ?? 10 }}px;
                     ">
                    One Hundred Fifty Pesos Only
                </div>

                {{-- CASHIER NAME --}}
                <div class="absolute text-center w-full"
                     style="
                        top: {{ $layout['cashier_name']['y'] ?? 0 }}mm;
                        left: {{ $layout['cashier_name']['x'] ?? 0 }}mm;
                        font-size: {{ $layout['cashier_name']['font'] ?? 12 }}px;
                     ">
                    ( Cashier Name )
                </div>

            </div>

        </div>

    </div>
</div>
