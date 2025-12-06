<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use App\Models\InventoryItem;

class ManageInventoryItems extends Component
{
    public $showForm = false;
    public $editId = null;

    public $name;
    public $code;
    public $category = 'veneer';
    public $unit = 'pcs';
    public $is_producible = true;
    public $is_repairable = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:255',
        'category' => 'required|string',
        'unit' => 'required|string',
        'is_producible' => 'boolean',
        'is_repairable' => 'boolean',
    ];

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);

        $this->editId = $id;
        $this->name = $item->name;
        $this->code = $item->code;
        $this->category = $item->category;
        $this->unit = $item->unit;
        $this->is_producible = $item->is_producible;
        $this->is_repairable = $item->is_repairable;

        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        InventoryItem::updateOrCreate(
            ['id' => $this->editId],
            [
                'name' => $this->name,
                'code' => $this->code,
                'category' => $this->category,
                'unit' => $this->unit,
                'is_producible' => $this->is_producible,
                'is_repairable' => $this->is_repairable,
            ]
        );

        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->name = '';
        $this->code = '';
        $this->category = 'veneer';
        $this->unit = 'pcs';
        $this->is_producible = true;
        $this->is_repairable = false;
    }

    public function render()
    {
        return view('livewire.inventory.manage-inventory-items', [
            'items' => InventoryItem::orderBy('name')->get()
        ]);
    }
}
