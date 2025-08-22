<?php

namespace App\Livewire\Offices;

use App\Livewire\BaseComponent;
use Livewire\Component;
use App\Models\Office;

class Index extends BaseComponent
{
    public $offices;
    public $showModal = false;
    public $officeId;
    public $name, $location;

    protected $rules = [
        'name' => 'required|string|max:255',
        'location' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->loadOffices();
    }

    public function loadOffices()
    {
        $this->offices = Office::orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $office = Office::findOrFail($id);
        $this->officeId = $office->id;
        $this->name = $office->name;
        $this->location = $office->location;
        $this->showModal = true;
    }

    public function delete($id)
    {
        $office = Office::find($id);

        if (!$office) {
            $this->dispatchBrowserEvent('toast', [
                'type' => 'error',
                'message' => 'Office not found.'
            ]);
            return;
        }

        $office->delete();

        $this->toast('success', 'Office deleted successfully.');

        $this->loadOffices();
    }

    public function save()
    {
        $this->validate();
        Office::updateOrCreate(
            ['id' => $this->officeId],
            ['name' => $this->name, 'location' => $this->location]
        );

        $this->showModal = false;
        $this->resetForm();
        $this->loadOffices();
        $this->toast('success', 'Success!');
    }

    public function resetForm()
    {
        $this->officeId = null;
        $this->name = '';
        $this->location = '';
    }

    public function render()
    {
        return view('livewire.offices.index');
    }
}
