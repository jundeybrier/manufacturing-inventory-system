<?php

namespace App\Livewire\Bom;

use App\Models\InventoryItem;
use Livewire\Component;

class BomIndex extends Component
{
    public $products;

    public function mount()
    {
        // Show all items that can be produced (finished goods)
        $this->products = InventoryItem::where('is_producible', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.bom.bom-index');
    }
}
