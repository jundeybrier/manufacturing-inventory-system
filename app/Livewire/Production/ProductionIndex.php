<?php

namespace App\Livewire\Production;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductionBatch;

class ProductionIndex extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $batches = ProductionBatch::with('item')
            ->when($this->search, function ($query) {
                $query->whereHas('item', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('batch_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('livewire.production.production-index', [
            'batches' => $batches,
        ]);
    }
}
