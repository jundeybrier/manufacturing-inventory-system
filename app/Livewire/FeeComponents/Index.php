<?php

namespace App\Livewire\FeeComponents;

use App\Livewire\BaseComponent;
use App\Models\FeeComponent;
use App\Models\Service;

class Index extends BaseComponent
{
    public $feeComponents;
    public $services;
    public $showModal = false;

    public $feeComponentId;
    public $service_id = '';
    public $name = '';
    public $base_amount = '';
    public $is_variable = false;
    public $currency = 'PHP';
    public $is_active = true;
    public $accounts = [];

    public function mount()
    {
        $this->loadFeeComponents();
        $this->services = Service::orderBy('name')->get();
        $this->accounts = \App\Models\Account::orderBy('name')->get();
    }

    public function loadFeeComponents()
    {
        $this->feeComponents = FeeComponent::with('service')->orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $fee = FeeComponent::findOrFail($id);
        $this->feeComponentId = $fee->id;
        $this->service_id = $fee->service_id;
        $this->name = $fee->name;
        $this->base_amount = $fee->base_amount;
        $this->is_variable = $fee->is_variable;
        $this->currency = $fee->currency;
        $this->is_active = $fee->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'base_amount' => 'nullable|numeric|min:0',
            'is_variable' => 'required|boolean',
            'currency' => 'required|string|max:5',
            'is_active' => 'required|boolean',
        ]);

        FeeComponent::updateOrCreate(
            ['id' => $this->feeComponentId],
            [
                'service_id' => $this->service_id,
                'name' => $this->name,
                'base_amount' => $this->base_amount,
                'is_variable' => $this->is_variable,
                'currency' => $this->currency,
                'is_active' => $this->is_active,
            ]
        );

        $this->toast('success', $this->feeComponentId ? 'Fee component updated!' : 'Fee component created!');
        $this->showModal = false;
        $this->resetForm();
        $this->loadFeeComponents();
    }

    public function delete($id)
    {
        $fee = FeeComponent::findOrFail($id);
        $fee->delete();
        $this->toast('success', 'Fee component deleted!');
        $this->loadFeeComponents();
    }

    public function resetForm()
    {
        $this->feeComponentId = null;
        $this->service_id = '';
        $this->name = '';
        $this->base_amount = '';
        $this->is_variable = false;
        $this->currency = 'PHP';
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.fee-components.index');
    }
}
