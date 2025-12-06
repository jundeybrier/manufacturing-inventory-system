<flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
<flux:navlist.item
    :href="route('production.index')"
    :current="request()->routeIs('production.index')"
    wire:navigate>
    <i class="fas fa-gear"></i> {{ __(' Production') }}
</flux:navlist.item>
<flux:navlist.item
    :href="route('bom.index')"
    :current="request()->routeIs('bom.index')"
    wire:navigate>
    <i class="fas fa-gear"></i> {{ __(' BOM') }}
</flux:navlist.item>
<flux:navlist.item
    :href="route('inventory-items.index')"
    :current="request()->routeIs('inventory-items.index')"
    wire:navigate>
    <i class="fas fa-boxes-stacked"></i> {{ __(' Inventory Items') }}
</flux:navlist.item>
<flux:navlist.item
    :href="route('stage-movements.index')"
    :current="request()->routeIs('stage-movements.index')"
    wire:navigate>
    <i class="fas fa-right-left"></i> {{ __('Stage Movement') }}
</flux:navlist.item>
<flux:navlist.item
    :href="route('users.index')"
    :current="request()->routeIs('users.index')"
    wire:navigate>
    <i class="fas fa-users"></i> {{ __('Users') }}
</flux:navlist.item>
