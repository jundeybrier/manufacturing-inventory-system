<?php

namespace App\Livewire\Bom;

use App\Models\InventoryItem;
use App\Models\BillOfMaterial;
use Livewire\Component;

class BomEditor extends Component
{
    public $product;
    public $materials;
    public $material_id;
    public $quantity;
    public $unit;

    public function mount($product)
    {
        $this->product = InventoryItem::findOrFail($product);
        $this->loadMaterials();
    }

    public function loadMaterials()
    {
        $this->materials = BillOfMaterial::where('inventory_item_id', $this->product->id)
            ->with('material')
            ->get();
    }

    public function addMaterial()
    {
        $this->validate([
            'material_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string',
        ]);

        BillOfMaterial::create([
            'inventory_item_id' => $this->product->id,
            'material_id' => $this->material_id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
        ]);

        $this->reset(['material_id', 'quantity', 'unit']);

        $this->loadMaterials();
    }

    public function deleteMaterial($id)
    {
        BillOfMaterial::find($id)?->delete();
        $this->loadMaterials();
    }

    public function render()
    {
        return view('livewire.bom.bom-editor', [
            'rawItems' => InventoryItem::where('is_finished_good', false)->get(),
        ]);
    }
}
