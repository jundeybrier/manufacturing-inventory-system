<?php

namespace App\Livewire;

use Livewire\Component;
use App\Jobs\SyncClosedSessionsJob;
use Illuminate\Support\Facades\Log;

class ManualSyncTrigger extends Component
{
    public $isSyncing = false;

    public function syncNow()
    {
        $this->isSyncing = true;

        try {
            dispatch(new SyncClosedSessionsJob());

            $this->dispatch('toast', type: 'success', message: 'Sync request submitted.');
        } catch (\Throwable $e) {
            Log::error('Manual Sync Failed', ['error' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error', message: 'Failed to initiate sync.');
        }

        $this->isSyncing = false;
    }

    public function render()
    {
        return view('livewire.manual-sync-trigger');
    }

    public function getUnsyncedProperty()
    {
        return \App\Models\CashierSession::whereNotNull('closed_at')
            ->whereNull('synced_at')
            ->count();
    }
}
