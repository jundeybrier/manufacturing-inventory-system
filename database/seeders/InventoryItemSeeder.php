<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Veneer samples
            ['name' => 'Composed Dry Veneer', 'category' => 'veneer', 'unit' => 'w/s'],
            ['name' => 'In-Process Dry Veneer', 'category' => 'veneer', 'unit' => 'w/s'],
            ['name' => 'Green Veneer', 'category' => 'veneer', 'unit' => 'w/s'],
            ['name' => 'Face & Back Veneer', 'category' => 'veneer', 'unit' => 'w/s'],

            // Pre-fab samples
            ['name' => 'Pre-Fab Panel 3.8mm', 'category' => 'pre_fab', 'unit' => 'pcs'],
            ['name' => 'Pre-Fab Panel 5.5mm', 'category' => 'pre_fab', 'unit' => 'pcs'],

            // Plywood samples
            ['name' => 'Plywood 5mm T1', 'category' => 'plywood', 'unit' => 'pcs', 'is_finished_good'=>1],
            ['name' => 'Plywood 10mm T1', 'category' => 'plywood', 'unit' => 'pcs', 'is_finished_good'=>1],
            ['name' => 'Plywood 18mm T1', 'category' => 'plywood', 'unit' => 'pcs', 'is_finished_good'=>1],
        ];

        foreach ($items as $item) {
            InventoryItem::create([
                'name' => $item['name'],
                'code' => null,
                'category' => $item['category'],
                'unit' => $item['unit'],
                'is_producible' => true,
                'is_repairable' => true,
            ]);
        }
    }
}
