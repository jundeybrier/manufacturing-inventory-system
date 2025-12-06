<?php

namespace App\Services;

use App\Models\StageInventory;
use App\Models\StageMovement;
use Illuminate\Support\Facades\DB;

class StageMovementService
{
    public function recordMovement(array $data): StageMovement
    {
        return DB::transaction(function () use ($data) {

            // 1. Create movement record
            $movement = StageMovement::create([
                'inventory_item_id'    => $data['inventory_item_id'],
                'from_stage_id'        => $data['from_stage_id'] ?? null,
                'to_stage_id'          => $data['to_stage_id'] ?? null,
                'quantity'             => $data['quantity'],
                'movement_date'        => $data['movement_date'],
                'remarks'              => $data['remarks'] ?? null,
                'production_order_id'  => $data['production_order_id'] ?? null,
            ]);

            // 2. Deduct from FROM stage
            if (!empty($data['from_stage_id'])) {
                $fromInv = StageInventory::where('inventory_item_id', $data['inventory_item_id'])
                    ->where('stage_id', $data['from_stage_id'])
                    ->firstOrFail();

                $fromInv->current_qty -= $data['quantity'];
                $fromInv->save();
            }

            // 3. Add to TO stage
            if (!empty($data['to_stage_id'])) {
                $toInv = StageInventory::where('inventory_item_id', $data['inventory_item_id'])
                    ->where('stage_id', $data['to_stage_id'])
                    ->firstOrFail();

                $toInv->current_qty += $data['quantity'];
                $toInv->save();
            }

            return $movement;
        });
    }
}
