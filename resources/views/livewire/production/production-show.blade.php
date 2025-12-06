<div class="p-6 space-y-8">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Production Batch #{{ $batch->id }}
        </h1>

        <a href="{{ route('production.index') }}"
           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
            ← Back
        </a>
    </div>

    {{-- BATCH SUMMARY --}}
    <div class="p-4 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Batch Summary</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Product:</div>
                <div class="font-medium">{{ $batch->item->name }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Batch Date:</div>
                <div class="font-medium">{{ $batch->batch_date }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Planned Qty:</div>
                <div class="font-medium">{{ $batch->planned_quantity }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Actual Output:</div>
                <div class="font-medium">{{ $batch->actual_output }}</div>
            </div>
        </div>
    </div>


    {{-- MATERIALS USED --}}
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Material Usage</h2>

        <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
            <table class="w-full border-collapse">
                <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th class="p-2 border">Material</th>
                    <th class="p-2 border text-center">From Stage</th>
                    <th class="p-2 border text-center">Quantity Used</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                @foreach ($materialsUsed as $m)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-2 border">{{ $m->material->name }}</td>
                        <td class="p-2 border text-center">
                            {{ optional($m->fromStage)->name ?? '—' }}
                        </td>
                        <td class="p-2 border text-center">{{ $m->quantity_used }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    {{-- OUTPUTS --}}
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Outputs</h2>

        <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
            <table class="w-full border-collapse">
                <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th class="p-2 border">Quantity</th>
                    <th class="p-2 border">To Stage</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                @foreach ($outputs as $o)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-2 border">{{ $o->quantity }}</td>
                        <td class="p-2 border">{{ optional($o->toStage)->name ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    {{-- VARIANCE ANALYSIS --}}
    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            Material Variance Analysis
        </h2>

        <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
            <table class="w-full border-collapse">
                <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th class="p-2 border">Material</th>
                    <th class="p-2 border text-center">Expected</th>
                    <th class="p-2 border text-center">Actual</th>
                    <th class="p-2 border text-center">Variance</th>
                    <th class="p-2 border text-center">Status</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                @foreach ($analysis as $a)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">

                        <td class="p-2 border">{{ $a['material'] }}</td>

                        <td class="p-2 border text-center">
                            {{ number_format($a['expected'], 3) }} {{ $a['unit'] }}
                        </td>

                        <td class="p-2 border text-center">
                            {{ number_format($a['actual'], 3) }} {{ $a['unit'] }}
                        </td>

                        <td class="p-2 border text-center">
                            {{ number_format($a['variance'], 3) }} {{ $a['unit'] }}
                        </td>

                        <td class="p-2 border text-center">
                            @if ($a['status'] === 'Overuse')
                                <span class="px-2 py-1 rounded bg-red-600 text-white">{{ $a['status'] }}</span>
                            @elseif ($a['status'] === 'Underuse')
                                <span class="px-2 py-1 rounded bg-yellow-500 text-white">{{ $a['status'] }}</span>
                            @else
                                <span class="px-2 py-1 rounded bg-green-600 text-white">{{ $a['status'] }}</span>
                            @endif
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>


</div>
