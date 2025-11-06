<div class="p-4 space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold flex items-center text-gray-900 dark:text-gray-100">
            <i class="fas fa-building mr-2"></i> Offices
        </h1>
        <flux:button size="sm" wire:click="create" class="flex items-center">
            <i class="fas fa-plus mr-1"></i> New Office
        </flux:button>
    </div>

    <!-- Offices Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
            <thead>
            <tr class="text-left">
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">UUID</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Name</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Location</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-center w-32 text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($offices as $office)
                <tr>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                        {{ $office->uuid }}
                    </td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                        {{ $office->name }}
                    </td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        {{ $office->location }}
                    </td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                        <flux:button size="xs" variant="outline" wire:click="edit({{ $office->id }})" class="mr-2">
                            <i class="fas fa-edit"></i>
                        </flux:button>
                        <flux:button size="xs" color="red" wire:click="delete({{ $office->id }})">
                            <i class="fas fa-trash"></i>
                        </flux:button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center p-4 text-gray-600 dark:text-gray-300">
                        No offices found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Slide-in Modal -->
    <x-modal-slide-over wire:model="showModal" title="{{ $officeId ? 'Edit Office' : 'Add Office' }}">
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:input label="Name" wire:model.defer="name" class="dark:text-gray-100" />
            <flux:input label="Location" wire:model.defer="location" class="dark:text-gray-100" />

            <div class="flex justify-end space-x-2">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button type="submit">
                    <i class="fas fa-save mr-1"></i> {{ $officeId ? 'Update' : 'Save' }}
                </flux:button>
            </div>
        </form>
    </x-modal-slide-over>
</div>
