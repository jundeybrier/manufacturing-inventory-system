<section class="w-full">
    @include('partials.services-heading')
    <div class="p-4 space-y-4">
        <x-services.layout :heading="__('Products')" :subheading="__('Define and manage products composed of multiple services')">
            <div class="p-4 space-y-4">
                <div class="flex justify-between items-center">
                    <flux:button size="sm" wire:click="create" class="flex items-center">
                        <i class="fas fa-plus mr-1"></i> New Product
                    </flux:button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                        <tr class="text-left">
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Name</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Description</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Total Amount</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Linked Services</th>
                            <th class="p-2 border border-gray-300 dark:border-gray-600 text-center w-32 text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $product)
                            @php
                                // Compute product total and variable flag
                                $total = 0;
                                $hasVariable = false;
                                foreach ($product->services as $service) {
                                    foreach ($service->feeComponents as $fee) {
                                        if ($fee->is_active) {
                                            if ($fee->is_variable) {
                                                $hasVariable = true;
                                            } else {
                                                $total += $fee->base_amount;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">{{ $product->name }}</td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ $product->description }}</td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100">
                                    ₱{{ number_format($total, 2) }}
                                    @if($hasVariable)
                                        <span class="inline-block ml-1 px-2 py-0.5 text-xs rounded border border-orange-500 text-orange-600 dark:text-orange-400">
                                            + variable
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($product->services as $service)
                                            <span
                                                class="px-2 py-1 border border-gray-400 dark:border-gray-500 rounded-full text-xs text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800">
                                                {{ $service->name }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 text-xs">No linked services</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="p-2 border border-gray-300 dark:border-gray-600 text-center">
                                    <flux:button size="xs" variant="outline" wire:click="edit({{ $product->id }})" class="mr-2">
                                        <i class="fas fa-edit"></i>
                                    </flux:button>
                                    <flux:button size="xs" color="red"
                                                 x-on:click="if (confirm('Delete this product?')) { $wire.delete({{ $product->id }}) }">
                                        <i class="fas fa-trash"></i>
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4 text-gray-600 dark:text-gray-300">
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Product Modal (Add/Edit) -->
                <x-modal-slide-over wire:model="showModal" title="{{ $productId ? 'Edit Product' : 'Add Product' }}">
                    <form wire:submit.prevent="save" class="space-y-4">
                        <flux:input label="Name" wire:model.defer="name" class="dark:text-gray-100" />
                        <flux:textarea label="Description" wire:model.defer="description" class="dark:text-gray-100" />

                        <div class="flex items-center">
                            <input type="checkbox" wire:model.defer="is_active" id="is_active" class="mr-2">
                            <label for="is_active" class="text-gray-700 dark:text-gray-300">Active</label>
                        </div>

                        <flux:field label="Linked Services">
                            <select wire:model.defer="selectedServiceIds" multiple class="w-full border rounded-md px-3 py-2 dark:bg-zinc-800">
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedServiceIds')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </flux:field>

                        <div class="flex justify-end space-x-2">
                            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                            <flux:button type="submit">
                                <i class="fas fa-save mr-1"></i> {{ $productId ? 'Update' : 'Save' }}
                            </flux:button>
                        </div>
                    </form>
                </x-modal-slide-over>
            </div>
        </x-services.layout>
    </div>
</section>
