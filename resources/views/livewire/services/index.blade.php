<section class="w-full">
    @include('partials.services-heading')
    <div class="p-4 space-y-4">
        <x-services.layout :heading="__('Services')" :subheading=" __('Update the services (Passport, Authentication, CRD)')">
            <div class="p-4 space-y-4">
                <div class="flex justify-between items-center">
                    <flux:button size="sm" wire:click="create" class="flex items-center">
                        <i class="fas fa-plus mr-1"></i> New Service
                    </flux:button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                        <tr class="text-left">
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Type</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Name</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Description</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Fees</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-center w-32 text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">{{ $service->type }}</td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">{{ $service->name }}</td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ $service->description }}</td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($service->feeComponents as $fee)
                                            <button
                                                class="px-2 py-1 border border-gray-400 dark:border-gray-500 rounded-full text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                                wire:click="editFee({{ $service->id }}, {{ $fee->id }})"
                                                title="Edit Fee">
                                                {{ $fee->name }}
                                                <span class="ml-1 text-xs text-gray-500">
                                            ({{ $fee->currency }}{{ $fee->is_variable ? '?' : number_format($fee->base_amount, 2) }})
                                        </span>
                                            </button>
                                        @empty
                                            <span class="text-gray-400 text-xs"></span>
                                        @endforelse
                                        <!-- Add Fee Button -->
                                        <button
                                            class="px-2 py-1 border border-dashed border-gray-100 rounded-xl text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-950 transition"
                                            wire:click="addFee({{ $service->id }})">
                                            <i class="fas fa-plus"></i> Add Fee
                                        </button>
                                    </div>
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600">
                                    <span>
                                        ₱{{ $service->feeComponents->where('is_variable', false)->where('is_active', true)->sum('base_amount') }}
                                    </span>
                                    @if($service->feeComponents->where('is_variable', true)->where('is_active', true)->count())
                                        <span class="inline-block ml-2 px-2 py-1 text-xs rounded border border-orange-500 text-orange-700 dark:text-orange-400">
                                            + variable
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                    <flux:button size="xs" variant="outline" wire:click="edit({{ $service->id }})" class="mr-2">
                                        <i class="fas fa-edit"></i>
                                    </flux:button>
                                    <flux:button size="xs" color="red"
                                                 x-on:click="if (confirm('Delete this service?')) { $wire.delete({{ $service->id }}) }">
                                        <i class="fas fa-trash"></i>
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-4 text-gray-600 dark:text-gray-300">
                                    No services found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Service Modal (for Add/Edit Service) -->
                <x-modal-slide-over wire:model="showModal" title="{{ $serviceId ? 'Edit Service' : 'Add Service' }}">
                    <form wire:submit.prevent="save" class="space-y-4">
                        <flux:select label="Type" wire:model="type">
                            @foreach (\App\Models\Service::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:input label="Name" wire:model.defer="name" class="dark:text-gray-100" />
                        <flux:input label="Description" wire:model.defer="description" class="dark:text-gray-100" />

                        <div class="flex justify-end space-x-2">
                            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                            <flux:button type="submit">
                                <i class="fas fa-save mr-1"></i> {{ $serviceId ? 'Update' : 'Save' }}
                            </flux:button>
                        </div>
                    </form>
                </x-modal-slide-over>

                <!-- Fee Modal (for Add/Edit Fee Component) -->
                <x-modal-slide-over wire:model="showFeeModal" title="{{ $editingFeeId ? 'Edit Fee' : 'Add Fee' }}">
                    <form wire:submit.prevent="saveFee" class="space-y-4">
                        <flux:input label="Name" wire:model.defer="fee_name" class="dark:text-gray-100" />
                        <flux:input label="Amount" wire:model.defer="fee_base_amount" type="number" step="0.01" class="dark:text-gray-100" />
                        <flux:select label="Account" wire:model.defer="fee_account_id" class="dark:text-gray-100">
                            <option value="">-- Select Account --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </flux:select>
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.defer="fee_is_variable" id="fee_is_variable" class="mr-2">
                            <label for="fee_is_variable" class="text-gray-700 dark:text-gray-300">Variable</label>
                        </div>
                        <flux:input label="Currency" wire:model.defer="fee_currency" class="dark:text-gray-100" />
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.defer="fee_is_active" id="fee_is_active" class="mr-2">
                            <label for="fee_is_active" class="text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <flux:button variant="ghost" wire:click="$set('showFeeModal', false)">Cancel</flux:button>
                            <flux:button type="submit">
                                <i class="fas fa-save mr-1"></i> {{ $editingFeeId ? 'Update' : 'Save' }}
                            </flux:button>
                        </div>
                    </form>
                </x-modal-slide-over>
            </div>

        </x-services.layout>
    </div>
</section>
