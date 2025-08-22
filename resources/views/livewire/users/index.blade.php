<div class="p-4 space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold flex items-center text-gray-900 dark:text-gray-100">
            <i class="fas fa-users mr-2"></i> Users
        </h1>
        <flux:button size="sm" wire:click="create">
            <i class="fas fa-plus mr-1"></i> New User
        </flux:button>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
            <thead>
            <tr class="text-left">
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Office</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Name</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Email</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Role</th>
                <th class="p-2 border border-gray-300 dark:border-gray-600 text-center w-32 text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">{{ $user->office?->name ?? '—' }}</td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ $user->email }}</td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        {{ $user->roles->pluck('name')->join(', ') }}
                    </td>
                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                        <flux:button size="xs" variant="outline" wire:click="edit({{ $user->id }})" class="mr-2">
                            <i class="fas fa-edit"></i>
                        </flux:button>
                        <flux:button size="xs" color="red" wire:click="delete({{ $user->id }})">
                            <i class="fas fa-trash"></i>
                        </flux:button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center p-4 text-gray-600 dark:text-gray-300">
                        No users found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Slide-in Modal -->
    <x-modal-slide-over wire:model="showModal" title="{{ $userId ? 'Edit User' : 'Add User' }}">
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:select label="Office" wire:model.defer="office_id" class="dark:text-gray-100">
                <option value="">-- Select Office --</option>
                @foreach($offices as $office)
                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                @endforeach
            </flux:select>
            <flux:input label="Name" wire:model.defer="name" />
            <flux:input label="Email" wire:model.defer="email" type="email" />
            <flux:input label="Password" wire:model.defer="password" type="password" placeholder="Leave blank to keep current password" />
            <flux:select label="Role" wire:model.defer="role">
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                @endforeach
            </flux:select>

            <div class="flex justify-end space-x-2">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button type="submit">
                    <i class="fas fa-save mr-1"></i> {{ $userId ? 'Update' : 'Save' }}
                </flux:button>
            </div>
        </form>
    </x-modal-slide-over>
</div>
