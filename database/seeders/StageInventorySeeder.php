<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Stage;
use App\Models\StageInventory;
use Illuminate\Database\Seeder;

class StageInventorySeeder extends Seeder
{
    public function run(): void
    {
        $items = InventoryItem::all();
        $stages = Stage::all();

        foreach ($items as $item) {
            foreach ($stages->where('category', $item->category) as $stage) {
                StageInventory::create([
                    'inventory_item_id' => $item->id,
                    'stage_id' => $stage->id,
                    'current_qty' => 0,
                ]);
            }
        }
    }
}
