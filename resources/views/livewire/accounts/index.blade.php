<section class="w-full">
    @include('partials.services-heading')
    <div class="p-4 space-y-4">
        <x-services.layout :heading="__('Accounts')" :subheading=" __('Update the accounts (Treasury, Provident, Office of the President)')">
            <div class="p-4 space-y-4">
                <div class="flex justify-between items-center">
                    <flux:button size="sm" wire:click="create" class="flex items-center">
                        <i class="fas fa-plus mr-1"></i> New Account
                    </flux:button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                        <tr class="text-left">
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Name</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Code</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Description</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-center text-gray-700 dark:text-gray-300">Active</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-center w-32 text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                                    {{ $account->name }}
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                    {{ $account->code }}
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                    {{ $account->description }}
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                    @if($account->is_active)
                                        <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle"></i></span>
                                    @else
                                        <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
                                    @endif
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                    <flux:button size="xs" variant="outline" wire:click="edit({{ $account->id }})" class="mr-2">
                                        <i class="fas fa-edit"></i>
                                    </flux:button>
                                    <flux:button size="xs" color="red"
                                                 x-on:click="if (confirm('Delete this account?')) { $wire.delete({{ $account->id }}) }">
                                        <i class="fas fa-trash"></i>
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4 text-gray-600 dark:text-gray-300">
                                    No accounts found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <x-modal-slide-over wire:model="showModal" title="{{ $accountId ? 'Edit Account' : 'Add Account' }}">
                    <form wire:submit.prevent="save" class="space-y-4">
                        <flux:input label="Name" wire:model.defer="name" class="dark:text-gray-100" />
                        <flux:input label="Code" wire:model.defer="code" class="dark:text-gray-100" />
                        <flux:input label="Description" wire:model.defer="description" class="dark:text-gray-100" />

                        <div class="flex items-center">
                            <input type="checkbox" wire:model.defer="is_active" id="is_active" class="mr-2">
                            <label for="is_active" class="text-gray-700 dark:text-gray-300">Active</label>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                            <flux:button type="submit">
                                <i class="fas fa-save mr-1"></i> {{ $accountId ? 'Update' : 'Save' }}
                            </flux:button>
                        </div>
                    </form>
                </x-modal-slide-over>
            </div>
        </x-services.layout>
    </div>
</section>
