<x-layouts.app :title="__('Dashboard')">
<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Production Batches
        </h1>

        <a href="{{ route('production.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
            + New Production
        </a>
    </div>

    {{-- SEARCH --}}
    <div>
        <input type="text"
               wire:model.debounce.300ms="search"
               placeholder="Search product..."
               class="w-full md:w-64 px-3 py-2 rounded-lg border-gray-300 dark:border-gray-700
                      dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
        <table class="w-full border-collapse">
            <thead class="bg-gray-200 dark:bg-gray-700">
            <tr>
                <th class="p-2 border text-left">Batch Date</th>
                <th class="p-2 border text-left">Product</th>
                <th class="p-2 border text-center">Planned Qty</th>
                <th class="p-2 border text-center">Actual Output</th>
                <th class="p-2 border text-center">Status</th>
                <th class="p-2 border text-center">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
            @forelse ($batches as $batch)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">

                    {{-- DATE --}}
                    <td class="p-2 border">
                        {{ \Carbon\Carbon::parse($batch->batch_date)->format('M d, Y') }}
                    </td>

                    {{-- PRODUCT --}}
                    <td class="p-2 border">
                        {{ $batch->item->name ?? '—' }}
                    </td>

                    {{-- PLANNED --}}
                    <td class="p-2 border text-center">
                        {{ number_format($batch->planned_quantity, 2) }}
                    </td>

                    {{-- OUTPUT --}}
                    <td class="p-2 border text-center">
                        {{ number_format($batch->actual_output ?? 0, 2) }}
                    </td>

                    {{-- STATUS --}}
                    <td class="p-2 border text-center">
                            <span class="px-2 py-1 rounded text-sm
                                @if($batch->status === 'Completed')
                                    bg-green-600 text-white
                                @elseif($batch->status === 'In Progress')
                                    bg-yellow-500 text-white
                                @else
                                    bg-gray-500 text-white
                                @endif">
                                {{ $batch->status }}
                            </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="p-2 border text-center">
                        <a href="{{ route('production.show', $batch->id) }}"
                           class="px-3 py-1 bg-blue-600 hover:bg-blue-700
                                      text-white rounded">
                            View
                        </a>
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="6"
                        class="p-4 text-center text-gray-500 dark:text-gray-400">
                        No production batches found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div>
        {{ $batches->links() }}
    </div>

</div>
</x-layouts.app>
