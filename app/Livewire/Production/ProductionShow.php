<?php

namespace App\Livewire\Production;

use Livewire\Component;
use App\Models\ProductionBatch;
use App\Models\BillOfMaterial;

class ProductionShow extends Component
{
    public $batch;
    public $bom = [];
    public $materialsUsed = [];
    public $outputs = [];
    public $analysis = [];

    public function mount($batchId)
    {
        $this->batch = ProductionBatch::with([
            'item',
            'items.material',
            'outputs',
        ])->findOrFail($batchId);

        $this->materialsUsed = $this->batch->items;
        $this->outputs = $this->batch->outputs;

        // BOM for variance analysis
        $this->bom = BillOfMaterial::where('inventory_item_id', $this->batch->inventory_item_id)->get();

        $this->computeAnalysis();
    }

    private function computeAnalysis()
    {
        $plannedQty = $this->batch->planned_quantity;

        foreach ($this->bom as $b) {
            $expected = $b->quantity * $plannedQty;
            $actual = $this->materialsUsed
                ->where('material_id', $b->material_id)
                ->sum('quantity_used');

            $variance = $actual - $expected;

            $this->analysis[] = [
                'material' => $b->material->name,
                'expected' => $expected,
                'actual'   => $actual,
                'variance' => $variance,
                'unit'     => $b->unit,
                'status'   =>
                    $variance > 0 ? 'Overuse' :
                        ($variance < 0 ? 'Underuse' : 'Exact'),
            ];
        }
    }

    public function render()
    {
        return view('livewire.production.production-show');
    }
}
