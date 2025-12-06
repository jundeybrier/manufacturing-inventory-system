<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Stage Management</h1>

        <button wire:click="create"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
            + New Stage
        </button>
    </div>


    {{-- STAGES TABLE --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-gray-700">
        <table class="w-full border-collapse">
            <thead class="bg-gray-200 dark:bg-gray-700">
            <tr>
                <th class="p-2 border">Seq</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Category</th>
                <th class="p-2 border">Description</th>
                <th class="p-2 border text-center">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
            @foreach ($stages as $stage)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">

                    <td class="p-2 border">{{ $stage->sequence }}</td>

                    <td class="p-2 border">{{ $stage->name }}</td>

                    {{-- CATEGORY --}}
                    <td class="p-2 border">
                        {{ \App\Models\StageCategory::label($stage->category) }}
                    </td>

                    <td class="p-2 border">
                        {{ $stage->description ?: '—' }}
                    </td>

                    <td class="p-2 border text-center space-x-2">

                        <button wire:click="edit({{ $stage->id }})"
                                class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded">
                            Edit
                        </button>

                        <button wire:click="delete({{ $stage->id }})"
                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded">
                            Delete
                        </button>

                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


    {{-- SLIDE-OVER FORM --}}
    <x-modal-slide-over wire:model="showForm">

        <x-slot name="title">
            {{ $editingId ? 'Edit Stage' : 'New Stage' }}
        </x-slot>

        <div class="space-y-4">

            {{-- CATEGORY --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Category</label>

                <select wire:model="category"
                        class="w-full px-3 py-2 rounded-lg border-gray-300 dark:border-gray-600
                           dark:bg-gray-800 dark:text-gray-200">

                    <option value="">-- Select Category --</option>

                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach

                </select>
            </div>

            {{-- NAME --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Name</label>

                <input type="text" wire:model="name"
                       class="w-full px-3 py-2 rounded-lg border-gray-300 dark:border-gray-600
                           dark:bg-gray-800 dark:text-gray-200">
            </div>

            {{-- SEQUENCE --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Sequence</label>

                <input type="number" wire:model="sequence"
                       class="w-full px-3 py-2 rounded-lg border-gray-300 dark:border-gray-600
                           dark:bg-gray-800 dark:text-gray-200">
            </div>

            {{-- DESCRIPTION --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Description</label>

                <textarea wire:model="description"
                          class="w-full px-3 py-2 rounded-lg border-gray-300 dark:border-gray-600
                           dark:bg-gray-800 dark:text-gray-200"></textarea>
            </div>

        </div>

            <button wire:click="save"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                Save
            </button>

    </x-modal-slide-over>

</div>
