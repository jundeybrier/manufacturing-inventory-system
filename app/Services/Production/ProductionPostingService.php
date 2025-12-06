<?php

namespace App\Services\Production;

use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\ProductionOutput;
use App\Models\StageMovement;
use Illuminate\Support\Facades\DB;

class ProductionPostingService
{
    /**
     * Post the production batch:
     * - Deduct materials from their stages
     * - Add finished goods to target stage
     */
    public function post(ProductionBatch $batch)
    {
        DB::transaction(function () use ($batch) {

            // 1. Deduct raw materials
            foreach ($batch->items as $item) {
                $this->deductMaterial($batch, $item);
            }

            // 2. Add finished goods
            foreach ($batch->outputs as $output) {
                $this->addFinishedGoods($batch, $output);
            }

            // 3. Mark batch as posted
            $batch->status = 'Posted';
            $batch->save();
        });
    }

    /**
     * Deduct raw materials based on actual usage.
     */
    private function deductMaterial(ProductionBatch $batch, ProductionBatchItem $item)
    {
        if (!$item->quantity_used || $item->quantity_used <= 0) {
            return;
        }

        StageMovement::create([
            'inventory_item_id' => $item->material_id,
            'from_stage_id'     => $item->from_stage_id,
            'to_stage_id'       => null, // consumed
            'movement_date'     => $batch->batch_date,
            'quantity'          => $item->quantity_used, // ALWAYS POSITIVE
            'remarks'           => 'Production use for Batch #' . $batch->id,
        ]);
    }

    /**
     * Move finished plywood sheets INTO the warehouse or chosen stage.
     */
    private function addFinishedGoods(ProductionBatch $batch, ProductionOutput $output)
    {
        if (!$output->quantity || $output->quantity <= 0) {
            return;
        }

        StageMovement::create([
            'inventory_item_id' => $batch->inventory_item_id,
            'from_stage_id'     => null,
            'to_stage_id'       => $output->to_stage_id,
            'movement_date'     => $batch->batch_date,
            'quantity'          => $output->quantity,
            'remarks'           => 'Production output for Batch #' . $batch->id,
        ]);
    }
}
