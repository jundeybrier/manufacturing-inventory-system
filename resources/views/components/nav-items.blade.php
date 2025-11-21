<flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
<flux:navlist.item
    :href="route('transactions.create')"
    :current="request()->routeIs('transactions.create')"
    wire:navigate>
    <i class="fas fa-cash-register mr-2"></i> {{ __('Cashiering Module') }}
</flux:navlist.item>
<flux:navlist.item
    :href="route('reports.index')"
    :current="request()->routeIs('reports.index')"
    wire:navigate>
    <i class="fas fa-clipboard mr-2"></i> {{ __('Reports') }}
</flux:navlist.item>
<flux:navlist.item
    :href="route('user.preferences')"
    :current="request()->routeIs('user.preferences')"
    wire:navigate>
    <i class="fas fa-gear mr-2"></i> {{ __('Preferences') }}
</flux:navlist.item>
@if(app_is_server())
    @role('admin')
    <flux:navlist.item
        :href="route('services.index')"
        :current="request()->routeIs('services.index')"
        wire:navigate>
        <i class="fas fa-cogs"></i></i> {{ __('Services') }}
    </flux:navlist.item>
    <flux:navlist.item
        :href="route('offices.index')"
        :current="request()->routeIs('offices.index')"
        wire:navigate>
        <i class="fas fa-building"></i></i> {{ __('Offices') }}
    </flux:navlist.item>
    <flux:navlist.item
        :href="route('users.index')"
        :current="request()->routeIs('users.index')"
        wire:navigate>
        <i class="fas fa-users"></i></i> {{ __('Users') }}
    </flux:navlist.item>
    @endrole
@endif
