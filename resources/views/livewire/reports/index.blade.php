<div>
    <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i class="fas fa-file-invoice text-zinc-600 dark:text-zinc-300"></i>
        Reports
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">

        {{-- Daily Collections Report --}}
        <div class="space-y-5">
            <div class="max-w-xl mx-auto py-10">
                <div class="p-6 rounded-xl border border-zinc-200 dark:border-zinc-700
                            bg-white dark:bg-zinc-900 space-y-4">

                    <h1 class="text-l font-bold mb-6 flex items-center gap-2">
                        Daily Collections Report
                    </h1>

                    <div class="space-y-4">

                        {{-- Report Date --}}
                        <flux:label>Date</flux:label>
                        <flux:input type="date" wire:model="date" />

                        {{-- Cashier/User --}}
                        @if($canSelectUser)
                            <div>
                                <flux:label>Cashier</flux:label>
                                <flux:select wire:model="userId">
                                    @foreach($officeUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                        @endif

                        <div class="flex justify-end">
                            <flux:button wire:click="generate">
                                <i class="fas fa-print mr-1"></i>
                                Generate
                            </flux:button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- CRR Report --}}
        <div class="space-y-5">
            <div class="max-w-xl mx-auto py-10">
                <div class="p-6 rounded-xl border border-zinc-200 dark:border-zinc-700
                    bg-white dark:bg-zinc-900 space-y-4">

                    <h1 class="text-l font-bold mb-6 flex items-center gap-2">
                        CRR Report
                    </h1>

                    <div class="space-y-4">

                        {{-- Date Range --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:label>Start Date</flux:label>
                                <flux:input type="date" wire:model="crrStartDate" />
                            </div>

                            <div>
                                <flux:label>End Date</flux:label>
                                <flux:input type="date" wire:model="crrEndDate" />
                            </div>
                        </div>

                        {{-- Select Account --}}
                        <div>
                            <flux:label>Account</flux:label>
                            <flux:select wire:model="crrAccountId">
                                @foreach($accounts as $acct)
                                    <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Select Cashier --}}
                        @if($canSelectUser)
                            <div>
                                <flux:label>Cashier</flux:label>
                                <flux:select wire:model="crrUserId">
                                    @foreach($officeUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                        @endif

                        <flux:select wire:model="format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                        </flux:select>

                        <div class="flex justify-end">
                            <flux:button wire:click="generateCrr">
                                <i class="fas fa-print mr-1"></i> Generate
                            </flux:button>
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
