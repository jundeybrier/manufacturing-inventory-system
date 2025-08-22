<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('services.index')" wire:navigate>{{ __('Services') }}</flux:navlist.item>
            <flux:navlist.item :href="route('accounts.index')" wire:navigate>{{ __('Accounts') }}</flux:navlist.item>
            <flux:navlist.item :href="route('fee-components.index')" wire:navigate>{{ __('Fee Components') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading><i class="fas fa-cogs mr-2"></i>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
