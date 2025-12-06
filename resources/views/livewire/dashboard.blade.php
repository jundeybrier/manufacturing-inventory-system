<div class="max-w-7xl mx-auto p-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Inventory Dashboard
        </h1>

        <div class="mt-4">
            <label class="font-medium text-gray-700 dark:text-gray-300">As of Date:</label>
            <input type="date" wire:model="asOfDate"
                   class="mt-1 px-3 py-2 rounded border border-gray-300 dark:border-gray-700
                          bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200" />
        </div>
    </div>

    {{-- GROUP ITEMS BY CATEGORY --}}
    @php $grouped = $items->groupBy('category'); @endphp

    @foreach ($grouped as $category => $groupItems)

        {{-- CATEGORY HEADING --}}
        <div class="mt-10">
            <h2 class="text-xl font-semibold mb-3 text-gray-800 dark:text-gray-200">
                {{ ucfirst(str_replace('_', ' ', $category)) }}
            </h2>

            {{-- TABLE --}}
            <div class="overflow-x-auto border dark:border-gray-700 rounded-lg shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 border-b text-left">Item</th>

                        @php
                            $allowedStages = $stagesByCategory[$category];
                        @endphp

                        @foreach ($allowedStages as $stage)
                            <th class="px-4 py-3 border-b text-center">
                                {{ $stage->name }}
                            </th>
                        @endforeach

                        <th class="px-4 py-3 border-b text-center">Total</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                    @foreach ($groupItems as $item)
                        @php
                            $stageRows = $results[$item->id]['stages'];
                            $total     = $results[$item->id]['total'];
                        @endphp

                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="px-4 py-3">{{ $item->name }}</td>

                            @foreach ($allowedStages as $stage)
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($stageRows[$stage->id] ?? 0, 3) }}
                                </td>
                            @endforeach

                            <td class="px-4 py-3 text-center font-semibold text-blue-600 dark:text-blue-400">
                                {{ number_format($total, 3) }}
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>

    @endforeach
</div>
