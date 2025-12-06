<div class="p-6 space-y-8">

    <!-- Step Navigation -->
    <div class="flex items-center gap-2">
        @foreach ([1,2,3,4,5,6] as $s)
            <div class="px-4 py-2 rounded-lg text-sm font-medium
                {{ $step == $s
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                }}">
                Step {{ $s }}
            </div>
        @endforeach
    </div>


    <!-- STEP 1: SELECT PRODUCT -->
    @if ($step == 1)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Select Product</h2>

            <select
                wire:model="selectedProduct"
                wire:change="$dispatch('selected-product-changed', { value: $event.target.value })"
                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200
                       rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">-- Select Product --</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <div class="flex justify-end">
                <x-primary-button wire:click="nextStep">
                    Next →
                </x-primary-button>
            </div>
        </div>
    @endif



    <!-- STEP 2: PLANNED QUANTITY -->
    @if ($step == 2)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Planned Quantity</h2>

            <input type="number" min="1" wire:model="plannedQuantity"
                   class="w-full border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2
                       dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">

            <div class="flex justify-between">
                <x-secondary-button wire:click="prevStep">← Back</x-secondary-button>
                <x-primary-button wire:click="nextStep">
                    Next →
                </x-primary-button>
            </div>
        </div>
    @endif



    <!-- STEP 3: BOM REFERENCE -->
    @if ($step == 3)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Bill of Materials (Reference Only)</h2>

            <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="p-2 border border-gray-300 dark:border-gray-700 text-left text-gray-700 dark:text-gray-200">
                            Material
                        </th>
                        <th class="p-2 border border-gray-300 dark:border-gray-700 text-left text-gray-700 dark:text-gray-200">
                            Qty per Sheet
                        </th>
                        <th class="p-2 border border-gray-300 dark:border-gray-700 text-left text-gray-700 dark:text-gray-200">
                            Total Needed
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                    @foreach ($bom as $b)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="p-2 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                                {{ $b->material->name }}
                            </td>

                            <td class="p-2 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                                {{ $b->quantity }} {{ $b->unit }}
                            </td>

                            <td class="p-2 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                                {{ $b->quantity * $plannedQuantity }} {{ $b->unit }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>


            <div class="flex justify-between">
                <x-secondary-button wire:click="prevStep">← Back</x-secondary-button>
                <x-primary-button wire:click="nextStep">Next →</x-primary-button>
            </div>
        </div>
    @endif



    <!-- STEP 4: ACTUAL MATERIAL USAGE -->
    @if ($step == 4)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Actual Material Usage</h2>

            <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="p-3 text-left border-b dark:border-gray-700">Material</th>
                        <th class="p-3 text-left border-b dark:border-gray-700">From Stage</th>
                        <th class="p-3 text-left border-b dark:border-gray-700">Actual Qty Used</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($bom as $b)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="p-3 border-b dark:border-gray-700">{{ $b->material->name }}</td>

                            <td class="p-3 border-b dark:border-gray-700">
                                <select wire:model="actualMaterials.{{ $b->material_id }}.stage"
                                        class="w-full border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1
                                               dark:bg-gray-900 dark:text-gray-200">
                                    <option value="">-- Select Stage --</option>
                                    @foreach (\App\Models\Stage::orderBy('sequence')->get() as $s)
                                        <option value="{{ $s->id }}">{{ $s->category.'-'.$s->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="p-3 border-b dark:border-gray-700">
                                <input type="number" min="0" step="0.01"
                                       wire:model="actualMaterials.{{ $b->material_id }}.quantity"
                                       class="w-full border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1
                                               dark:bg-gray-900 dark:text-gray-200">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between">
                <x-secondary-button wire:click="prevStep">← Back</x-secondary-button>
                <x-primary-button wire:click="nextStep">Next →</x-primary-button>
            </div>
        </div>
    @endif



    <!-- STEP 5: ACTUAL OUTPUT -->
    @if ($step == 5)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Actual Output</h2>

            <input type="number" min="1" wire:model="actualOutputs.0.quantity"
                   class="w-full border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2
                       dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">

            <div class="flex justify-between">
                <x-secondary-button wire:click="prevStep">← Back</x-secondary-button>

                <x-primary-button wire:click="nextStep"
                                  >
                    Next →
                </x-primary-button>
            </div>
        </div>
    @endif



    <!-- STEP 6: REVIEW & SAVE -->
    @if ($step == 6)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Review & Save</h2>

            <x-primary-button wire:click="saveProduction">
                Save Production
            </x-primary-button>

            <x-secondary-button class="ml-2" wire:click="prevStep">← Back</x-secondary-button>
        </div>
    @endif

</div>
