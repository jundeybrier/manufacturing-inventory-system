<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        // ===========================
        // 1. Veneer Stages
        // ===========================
        $veneerStages = [
            'Composed Dry Veneer',
            'In-Process Dry Veneer',
            'Green Veneer',
            'Face & Back',
            'Clipping',
            'Repair',
            'Repaired',
            'Scrap',
        ];

        foreach ($veneerStages as $i => $name) {
            Stage::create([
                'name' => $name,
                'category' => 'veneer',
                'sequence' => $i + 1,
            ]);
        }

        // ===========================
        // 2. Pre-Fab Panel Stages
        // ===========================
        $prefabStages = [
            'For Final',
            'For Grading',
            'For Sanding',
            'For Repair',
            'For 2nd Process',
            'Repaired',
            'Scrap',
        ];

        foreach ($prefabStages as $i => $name) {
            Stage::create([
                'name' => $name,
                'category' => 'pre_fab',
                'sequence' => $i + 1,
            ]);
        }

        // ===========================
        // 3. Plywood Stages
        // ===========================
        $plywoodStages = [
            'Crated Stocks',
            'For Grading',
            'For Sizing',
            'For Sanding',
            'Loose Panels',
            'For Repair',
            'Repaired',
            'Finished Goods',
            'Scrap',
        ];

        foreach ($plywoodStages as $i => $name) {
            Stage::create([
                'name' => $name,
                'category' => 'plywood',
                'sequence' => $i + 1,
            ]);
        }
    }
}
