<?php

namespace App\Livewire\Transactions;

use App\Models\CashierSession;
use Livewire\Component;

class SessionStatus extends Component
{

    public $activeSession;
    public bool $alreadyOpenedToday = false;

    public function mount()
    {
        $user = auth()->user();
        $this->activeSession = CashierSession::where('user_id', auth()->id())
            ->whereNull('closed_at')
            ->latest()
            ->first();

        $this->alreadyOpenedToday = CashierSession::where('user_id', auth()->id())
            ->whereDate('opened_at', now()->toDateString())
            ->exists();
    }

    public function render()
    {
        return view('livewire.transactions.session-status');
    }
}
