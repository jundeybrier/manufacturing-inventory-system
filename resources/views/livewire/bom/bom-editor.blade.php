<div class="p-6 space-y-6">

    <h1 class="text-xl font-bold">
        BOM for {{ $product->name }}
    </h1>

    <!-- Add Material Form -->
    <div class="space-y-3 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">

        <div>
            <label class="text-sm font-semibold">Material</label>
            <select wire:model="material_id"
                    class="w-full border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2
                       dark:bg-gray-900 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Select Material --</option>
                @foreach ($rawItems as $m)
                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold">Quantity per Sheet</label>
            <input type="number" wire:model="quantity" step="0.01"
                   class="w-full border-gray-300 dark:border-gray-700 rounded px-2 py-1">
        </div>

        <div>
            <label class="text-sm font-semibold">Unit (optional)</label>
            <input type="text" wire:model="unit"
                   class="w-full border-gray-300 dark:border-gray-700 rounded px-2 py-1">
        </div>

        <div class="text-right">
            <button wire:click="addMaterial"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
                Add Material
            </button>
        </div>
    </div>

    <!-- Existing BOM Table -->
    <div class="overflow-auto border border-gray-300 dark:border-gray-700 rounded-lg">
        <table class="w-full text-sm">
            <thead class="bg-gray-200 dark:bg-gray-700">
            <tr>
                <th class="p-2 text-left">Material</th>
                <th class="p-2 text-left">Quantity</th>
                <th class="p-2 text-left">Unit</th>
                <th class="p-2 text-center">Action</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($materials as $row)
                <tr class="border-b dark:border-gray-700">
                    <td class="p-2">{{ $row->material->name }}</td>
                    <td class="p-2">{{ $row->quantity }}</td>
                    <td class="p-2">{{ $row->unit }}</td>
                    <td class="p-2 text-center">
                        <button wire:click="deleteMaterial({{ $row->id }})"
                                class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>

</div>
