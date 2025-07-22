<?php

namespace App\Livewire\Transactions;

use Livewire\Component;

class BreakdownForm extends Component
{
    public $sessionId;
    public $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 1];
    public $counts = [];

    public function mount($sessionId)
    {
        $this->sessionId = $sessionId;
        $this->counts = array_fill(0, count($this->denominations), 0);
    }

    public function save()
    {
        foreach ($this->counts as $index => $qty) {
            $qty = intval($qty);
            if ($qty > 0) {
                CashCollection::create([
                    'cashiering_session_id' => $this->sessionId,
                    'denomination' => $this->denominations[$index],
                    'quantity' => $qty,
                    'total_amount' => $this->denominations[$index] * $qty,
                ]);
            }
        }

        // Close the session
        CashieringSession::where('id', $this->sessionId)->update([
            'closed_at' => now(),
        ]);

        $this->dispatch('sessionClosed'); // optional event to notify parent
        $this->dispatch('close'); // if your modal listens to this
    }

}
