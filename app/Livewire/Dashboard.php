<?php

namespace App\Livewire;

use App\Models\InventoryItem;
use App\Models\Stage;
use App\Models\StageMovement;
use Livewire\Component;

class Dashboard extends Component
{
    public $asOfDate;
    public $items;
    public $stages;

    // Cached filtered stages per category
    public $stagesByCategory = [];

    public $results = [];

    public function mount()
    {
        $this->asOfDate = now()->toDateString();

        // Preload items and stages
        $this->items  = InventoryItem::orderBy('category')->orderBy('name')->get();
        $this->stages = Stage::orderBy('sequence')->get();

        // Pre-group stages by category (for performance)
        $this->prepareStageFilters();

        $this->compute();
    }

    public function updatedAsOfDate()
    {
        $this->compute();
    }

    /**
     * Prepares a mapping:
     *  $this->stagesByCategory['veneer'] = [stage1, stage2]
     *  $this->stagesByCategory['plywood'] = [...]
     */
    private function prepareStageFilters()
    {
        $categories = $this->items->pluck('category')->unique();

        foreach ($categories as $cat) {
            $this->stagesByCategory[$cat] = $this->stages->filter(function ($stage) use ($cat) {
                return $stage->category === $cat
                    || $stage->category === 'all'
                    || $stage->category === null;
            });
        }
    }

    private function compute()
    {
        $this->results = [];

        foreach ($this->items as $item) {

            $allowedStages = $this->stagesByCategory[$item->category];

            $stageTotals = [];

            foreach ($allowedStages as $stage) {

                $totalIn = StageMovement::where('inventory_item_id', $item->id)
                    ->where('to_stage_id', $stage->id)
                    ->whereDate('movement_date', '<=', $this->asOfDate)
                    ->sum('quantity');

                $totalOut = StageMovement::where('inventory_item_id', $item->id)
                    ->where('from_stage_id', $stage->id)
                    ->whereDate('movement_date', '<=', $this->asOfDate)
                    ->sum('quantity');

                $stageTotals[$stage->id] = $totalIn - $totalOut;
            }

            $this->results[$item->id] = [
                'stages' => $stageTotals,
                'total'  => array_sum($stageTotals),
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
