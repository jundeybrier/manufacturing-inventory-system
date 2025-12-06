<?php

namespace App\Http\Controllers;

use App\Models\StageInventory;
use App\Models\StageMovement;
use App\Models\Stage;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Services\StageMovementService;

class StageMovementController extends Controller
{
    public function index()
    {
        $movements = StageMovement::with(['item', 'fromStage', 'toStage'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('stage-movements.index', compact('movements'));
    }

    public function create()
    {
        return view('stage-movements.create', [
            'items'  => InventoryItem::orderBy('name')->get(),
            'stages' => Stage::orderBy('sequence')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'from_stage_id'     => 'nullable|exists:stages,id',
            'to_stage_id'       => 'nullable|exists:stages,id',
            'quantity'          => 'required|numeric|min:0.001',
            'movement_date'     => 'required|date',
            'remarks'           => 'nullable|string|max:255',
        ]);

        $itemId   = $validated['inventory_item_id'];
        $fromId   = $validated['from_stage_id'] ?? null;
        $toId     = $validated['to_stage_id'] ?? null;
        $qty      = $validated['quantity'];

        // ❗ Cannot move to and from same stage
        if ($fromId && $toId && $fromId == $toId) {
            return back()->with('error', 'From Stage and To Stage cannot be the same.');
        }

        // ❗ Cannot move with both FROM and TO empty
        if (!$fromId && !$toId) {
            return back()->with('error', 'Please specify a From stage or a To stage.');
        }

        // ❗ If FROM stage is selected, ensure enough stock exists
//        if ($fromId) {
//            $currentFromQty = StageInventory::getQty($itemId, $fromId);
//
//            if ($currentFromQty < $qty) {
//                return back()->with(
//                    'error',
//                    "Not enough stock in the selected From Stage.
//                 Available: {$currentFromQty}, Required: {$qty}"
//                );
//            }
//        }

        // 🔄 Record the movement
        $movement = StageMovement::create($validated);

        // 🔽 Deduct FROM stage
        if ($fromId) {
            StageInventory::deduct($itemId, $fromId, $qty);
        }

        // 🔼 Add TO stage
        if ($toId) {
            StageInventory::add($itemId, $toId, $qty);
        }

        return redirect()
            ->route('stage-movements.index')
            ->with('success', 'Movement recorded successfully.');
    }
}
