<section class="w-full">
    @include('partials.services-heading')
    <div class="p-4 space-y-4">
        <x-services.layout :heading="__('Fee Components')" :subheading=" __('Update the fee components')">
            <div class="p-4 space-y-4">
                <div class="p-4 space-y-4">
                    <div class="flex justify-between items-center">
                        <flux:button size="sm" wire:click="create" class="flex items-center">
                            <i class="fas fa-plus mr-1"></i> New Fee Component
                        </flux:button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
                            <thead>
                            <tr class="text-left">
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Service</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Name</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Base Amount</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Variable</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Currency</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Account</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-center text-gray-700 dark:text-gray-300">Active</th>
                                <th class="p-2 border border-gray-300 dark:border-gray-600 text-center w-32 text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($feeComponents as $fee)
                                <tr>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                        {{ $fee->service?->name ?? '—' }}
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                                        {{ $fee->name }}
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                        {{ $fee->base_amount === null ? '—' : number_format($fee->base_amount, 2) }}
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                        @if($fee->is_variable)
                                            <span class="text-indigo-600 dark:text-indigo-400"><i class="fas fa-check-circle"></i></span>
                                        @else
                                            <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
                                        @endif
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                        {{ $fee->currency }}
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                        {{ $fee->account?->name ?? '—' }}
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                        @if($fee->is_active)
                                            <span class="text-green-600 dark:text-green-400"><i class="fas fa-check-circle"></i></span>
                                        @else
                                            <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
                                        @endif
                                    </td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                        <flux:button size="xs" variant="outline" wire:click="edit({{ $fee->id }})" class="mr-2">
                                            <i class="fas fa-edit"></i>
                                        </flux:button>
                                        <flux:button size="xs" color="red"
                                                     x-on:click="if (confirm('Delete this fee component?')) { $wire.delete({{ $fee->id }}) }">
                                            <i class="fas fa-trash"></i>
                                        </flux:button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center p-4 text-gray-600 dark:text-gray-300">
                                        No fee components found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <x-modal-slide-over wire:model="showModal" title="{{ $feeComponentId ? 'Edit Fee Component' : 'Add Fee Component' }}">
                        <form wire:submit.prevent="save" class="space-y-4">
                            <flux:select label="Service" wire:model.defer="service_id" class="dark:text-gray-100">
                                <option value="">-- Select Service --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:input label="Name" wire:model.defer="name" class="dark:text-gray-100" />
                            <flux:input label="Base Amount" wire:model.defer="base_amount" type="number" step="0.01" class="dark:text-gray-100" />
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.defer="is_variable" id="is_variable" class="mr-2">
                                <label for="is_variable" class="text-gray-700 dark:text-gray-300">Variable</label>
                            </div>
                            <flux:input label="Currency" wire:model.defer="currency" class="dark:text-gray-100" />

                            <flux:select label="Account" wire:model.defer="account_id" class="dark:text-gray-100">
                                <option value="">-- Select Account --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </flux:select>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model.defer="is_active" id="is_active" class="mr-2">
                                <label for="is_active" class="text-gray-700 dark:text-gray-300">Active</label>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                                <flux:button type="submit">
                                    <i class="fas fa-save mr-1"></i> {{ $feeComponentId ? 'Update' : 'Save' }}
                                </flux:button>
                            </div>
                        </form>
                    </x-modal-slide-over>
                </div>
            </div>
        </x-services.layout>
    </div>
</section>
