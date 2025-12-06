<?php

namespace App\Livewire\Stages;

use Livewire\Component;
use App\Models\Stage;
use App\Models\StageCategory;

class StageManager extends Component
{
    public $stages = [];
    public $categories = [];

    public $showForm = false;
    public $editingId = null;

    public $name = '';
    public $sequence = 1;
    public $description = '';
    public $category = '';

    public function mount()
    {
        $this->loadStages();
        $this->categories = StageCategory::list(); // veneer, pre_fab, plywood
    }

    public function loadStages()
    {
        $this->stages = Stage::orderBy('sequence')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $stage = Stage::findOrFail($id);

        $this->editingId   = $stage->id;
        $this->name        = $stage->name;
        $this->category    = $stage->category;
        $this->sequence    = $stage->sequence;
        $this->description = $stage->description;

        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->category = '';
        $this->sequence = 1;
        $this->description = '';

        $this->showForm = false;
    }

    public function save()
    {
        $this->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|in:' . implode(',', array_keys(StageCategory::list())),
            'sequence'    => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Stage::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name'        => $this->name,
                'category'    => $this->category,
                'sequence'    => $this->sequence,
                'description' => $this->description,
            ]
        );

        $this->resetForm();
        $this->loadStages();

        session()->flash('success', 'Stage saved successfully.');
    }

    public function delete($id)
    {
        Stage::destroy($id);
        $this->loadStages();
    }

    public function render()
    {
        return view('livewire.stages.stage-manager');
    }
}

