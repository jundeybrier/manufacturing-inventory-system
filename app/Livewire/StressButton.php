<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class StressButton extends Component
{
    public $clickCount;

    public function mount()
    {
        $this->clickCount = Auth::user()->click_count ?? 0;
    }

    public function increase()
    {
        $user = Auth::user();
        $user->increment('click_count');
        $this->clickCount = $user->click_count;
    }

    public function render()
    {
        return view('livewire.stress-button');
    }
}
