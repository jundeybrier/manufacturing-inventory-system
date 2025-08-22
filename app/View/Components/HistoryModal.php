<?php

namespace App\View\Components;

use Illuminate\View\Component;

class HistoryModal extends Component
{
    public $show;
    public $transactions;

    public function __construct($show = false, $transactions = [])
    {
        $this->show = $show;
        $this->transactions = $transactions;
    }

    public function render()
    {
        return view('components.history-modal');
    }
}
