<div class="p-6 space-y-6">

    <h1 class="text-2xl font-bold">Bill of Materials</h1>
    <p class="text-gray-600 dark:text-gray-400">
        Select a product to view or edit its BOM recipe.
    </p>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
            <thead class="bg-gray-200 dark:bg-gray-800 text-left">
            <tr>
                <th class="p-3 font-semibold">Product</th>
                <th class="p-3 font-semibold text-center">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($products as $product)
                <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                    <td class="p-3">
                        {{ $product->name }}
                    </td>

                    <td class="p-3 text-center">
                        <a href="{{ route('bom.edit', $product->id) }}"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                            Edit BOM
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
