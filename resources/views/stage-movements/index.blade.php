<x-layouts.app :title="__('Stage Movements')">
<div class="max-w-7xl mx-auto px-6 py-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold">Stage Movements</h1>

        <a href="{{ route('stage-movements.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Record Movement
        </a>
    </div>

    {{-- FILTER / SEARCH --}}
    <form method="GET" class="mb-6 flex gap-3 items-end">
        <div>
            <label class="block font-semibold mb-1">Filter by Date</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="border rounded px-3 py-2 w-48">
        </div>

        <button class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
            Apply
        </button>

        @if(request('date'))
            <a href="{{ route('stage-movements.index') }}"
               class="text-sm text-blue-600 underline ml-2 mt-2">
                Clear Filter
            </a>
        @endif
    </form>

    {{-- TABLE --}}
    <div class="overflow-x-auto rounded border border-gray-300 dark:border-gray-700">
        <table class="w-full border-collapse">
            <thead class="bg-gray-100 dark:bg-gray-800">
            <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th class="px-3 py-2 border-b">Date</th>
                <th class="px-3 py-2 border-b">Item</th>
                <th class="px-3 py-2 border-b">From</th>
                <th class="px-3 py-2 border-b">To</th>
                <th class="px-3 py-2 border-b text-right">Qty</th>
                <th class="px-3 py-2 border-b">Remarks</th>
            </tr>
            </thead>

            <tbody class="text-sm">
            @forelse($movements as $m)
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-900">
                    {{-- DATE --}}
                    <td class="px-3 py-2">
                        {{ $m->movement_date->format('M d, Y') }}
                    </td>

                    {{-- ITEM --}}
                    <td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-300">
                        {{ $m->item->name }}
                    </td>

                    {{-- FROM --}}
                    <td class="px-3 py-2">
                        @if($m->fromStage)
                            <span class="px-2 py-1 inline-block bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 rounded text-xs">
                            {{ $m->fromStage->name }}
                        </span>
                        @else
                            <span class="text-gray-500">—</span>
                        @endif
                    </td>

                    {{-- TO --}}
                    <td class="px-3 py-2">
                        @if($m->toStage)
                            <span class="px-2 py-1 inline-block bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200 rounded text-xs">
                            {{ $m->toStage->name }}
                        </span>
                        @else
                            <span class="text-gray-500">—</span>
                        @endif
                    </td>

                    {{-- QUANTITY --}}
                    <td class="px-3 py-2 text-right font-semibold">
                        {{ number_format($m->quantity, 3) }}
                    </td>

                    {{-- REMARKS --}}
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                        {{ $m->remarks ?? '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-6 text-gray-500">
                        No movements found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $movements->links() }}
    </div>
</div>
</x-layouts.app>
