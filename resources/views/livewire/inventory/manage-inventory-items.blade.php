<div class="max-w-6xl mx-auto p-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Inventory Items
        </h1>

        <button wire:click="create"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                       text-white px-4 py-2 rounded-lg shadow-sm transition">
            <i class="fa-solid fa-plus"></i>
            Add Item
        </button>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm">
        <table class="w-full table-auto text-sm">
            <thead class="bg-gray-100 dark:bg-gray-800">
            <tr class="text-left font-semibold text-gray-700 dark:text-gray-300 text-sm">
                <th class="px-4 py-3 border-b">Name</th>
                <th class="px-4 py-3 border-b">Category</th>
                <th class="px-4 py-3 border-b">Unit</th>
                <th class="px-4 py-3 border-b">Producible?</th>
                <th class="px-4 py-3 border-b">Repairable?</th>
                <th class="px-4 py-3 border-b text-right">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                    <td class="px-4 py-3 text-gray-900 dark:text-gray-200">
                        {{ $item->name }}
                    </td>

                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs font-medium
                            @class([
                                'bg-blue-100 text-blue-700' => $item->category === 'veneer',
                                'bg-green-100 text-green-700' => $item->category === 'pre_fab',
                                'bg-purple-100 text-purple-700' => $item->category === 'plywood',
                                'bg-gray-200 text-gray-700' => $item->category === 'raw_material',

                                'dark:bg-blue-900 dark:text-blue-200' => $item->category === 'veneer',
                                'dark:bg-green-900 dark:text-green-200' => $item->category === 'pre_fab',
                                'dark:bg-purple-900 dark:text-purple-200' => $item->category === 'plywood',
                                'dark:bg-gray-800 dark:text-gray-300' => $item->category === 'raw_material',
                            ])">
                            {{ ucfirst(str_replace('_', ' ', $item->category)) }}
                        </span>
                    </td>

                    <td class="px-4 py-3">{{ $item->unit }}</td>

                    <td class="px-4 py-3">
                        @if($item->is_producible)
                            <span class="text-green-600 dark:text-green-400 font-semibold">Yes</span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">No</span>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        @if($item->is_repairable)
                            <span class="text-blue-600 dark:text-blue-400 font-semibold">Yes</span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">No</span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-right">
                        <button wire:click="edit({{ $item->id }})"
                                class="text-blue-600 dark:text-blue-400 hover:underline">
                            Edit
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- SLIDE-IN MODAL --}}
    <x-modal-slide-over wire:model="showForm">
        <h2 class="text-lg font-bold mb-4">
            {{ $editId ? 'Edit Item' : 'Add Item' }}
        </h2>

        <div class="space-y-4">

            {{-- NAME --}}
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" wire:model.defer="name"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200">
            </div>

            {{-- CODE --}}
            <div>
                <label class="block text-sm font-medium mb-1">Code (optional)</label>
                <input type="text" wire:model.defer="code"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200">
            </div>

            {{-- CATEGORY --}}
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select wire:model.defer="category"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200">
                    <option value="veneer">Veneer</option>
                    <option value="pre_fab">Pre-Fab</option>
                    <option value="plywood">Plywood</option>
                    <option value="raw_material">Raw Material</option>
                </select>
            </div>

            {{-- UNIT --}}
            <div>
                <label class="block text-sm font-medium mb-1">Unit</label>
                <input type="text" wire:model.defer="unit"
                       placeholder="pcs, sheets, cu.ft., sq.m..."
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200">
            </div>

            {{-- PRODUCIBLE --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="is_producible"
                       class="rounded border-gray-300 dark:border-gray-700">
                <label class="text-sm font-medium">Is Producible?</label>
            </div>

            {{-- REPAIRABLE --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="is_repairable"
                       class="rounded border-gray-300 dark:border-gray-700">
                <label class="text-sm font-medium">Is Repairable?</label>
            </div>

        </div>

        <div class="mt-6">
            <button wire:click="save"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Save
            </button>
        </div>
    </x-modal-slide-over>

</div>
