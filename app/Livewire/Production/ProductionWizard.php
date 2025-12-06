<?php

namespace App\Livewire\Production;

use App\Models\InventoryItem;
use App\Models\BillOfMaterial;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\ProductionOutput;
use App\Models\Stage;
use Livewire\Component;
use Livewire\Attributes\On;

class ProductionWizard extends Component
{
    public $step = 1;

    public $products;
    public $selectedProduct;
    public $plannedQuantity;

    public $bom = [];
    public $actualMaterials = [];
    public $actualOutputs = [];

    public $finishedGoodsStageId;

    public function mount()
    {
        $this->products = InventoryItem::where('is_finished_good', true)->get();
        $this->finishedGoodsStageId = Stage::where('name', 'For Sanding')->value('id');
    }

    public function nextStep()
    {
        if ($this->step == 1 && $this->selectedProduct) {
            $this->bom = BillOfMaterial::where('inventory_item_id', $this->selectedProduct)->get();
        }

        if ($this->step == 2 && $this->plannedQuantity > 0) {
            foreach ($this->bom as $b) {
                $this->actualMaterials[$b->material_id] = [
                    'quantity' => $b->quantity * $this->plannedQuantity,
                    'stage'    => null,
                ];
            }
        }

        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function saveProduction()
    {
        try {

            logger()->info('🔵 Starting saveProduction()', [
                'selectedProduct'   => $this->selectedProduct,
                'plannedQuantity'   => $this->plannedQuantity,
                'actualMaterials'   => $this->actualMaterials,
                'actualOutputs'     => $this->actualOutputs,
            ]);

            // --- TRY CREATING BATCH ---
            $batch = ProductionBatch::create([
                'batch_date'        => today(),
                'inventory_item_id' => $this->selectedProduct,
                'planned_quantity'  => $this->plannedQuantity,
                'status'            => 'Completed',
            ]);

            logger()->info('🟢 Batch created', ['batch' => $batch]);

            // --- MATERIAL LOOP ---
            foreach ($this->actualMaterials as $materialId => $values) {
                logger()->info('🔸 Material Loop', [
                    'materialId' => $materialId,
                    'values' => $values
                ]);

                if (empty($values['quantity']) || $values['quantity'] <= 0) {
                    continue;
                }

                ProductionBatchItem::create([
                    'production_batch_id' => $batch->id,
                    'material_id'         => $materialId,
                    'from_stage_id'       => $values['stage'] ?? null,
                    'quantity_used'       => $values['quantity'],
                    'unit'                => 'pcs',
                ]);
            }

            // --- OUTPUT LOOP ---
            foreach ($this->actualOutputs as $output) {
                logger()->info('🔹 Output Loop', ['output' => $output]);

                if (empty($output['quantity']) || $output['quantity'] <= 0) {
                    continue;
                }

                ProductionOutput::create([
                    'production_batch_id' => $batch->id,
                    'quantity'            => $output['quantity'],
                    'to_stage_id'         => $this->finishedGoodsStageId,
                ]);
            }

            // --- UPDATE BATCH TOTAL ---
            $batch->actual_output = array_sum(array_column($this->actualOutputs, 'quantity'));
            $batch->save();

            logger()->info('🟢 Batch updated with actual_output');

            // --- POSTING SERVICE ---
            (new \App\Services\Production\ProductionPostingService)->post($batch);

            logger()->info('🟢 Posting service completed');

            session()->flash('success', 'Production batch saved successfully!');
            return redirect()->route('production.index');

        } catch (\Throwable $e) {
            logger()->error('❌ Production save error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            dd($e->getMessage(), $e->getLine(), $e->getFile());
        }
    }


    public function render()
    {
        return view('livewire.production.production-wizard');
    }
}
