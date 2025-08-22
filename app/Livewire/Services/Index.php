<?php

namespace App\Livewire\Services;

use App\Livewire\BaseComponent; // If you have your toast() helper here
use App\Models\Service;
use Livewire\Component;

class Index extends BaseComponent
{
    public $services;
    public $showModal = false;
    public $serviceId;
    public $name = '';
    public $type = null;
    public $description = '';
    public $is_active = true;

    //fees modal
    public $showFeeModal = false;
    public $currentServiceId;
    public $editingFeeId = null;
    public $fee_account_id = null;

    public $fee_name = '';
    public $fee_base_amount = '';
    public $fee_is_variable = false;
    public $fee_currency = 'PHP';
    public $fee_is_active = true;
    public $accounts = [];

    public function mount()
    {
        $this->loadServices();
        $this->accounts = \App\Models\Account::orderBy('name')->get();
    }

    public function addFee($serviceId)
    {
        $this->resetFeeForm();
        $this->currentServiceId = $serviceId;
        $this->showFeeModal = true;
    }

    public function editFee($serviceId, $feeId)
    {
        $this->currentServiceId = $serviceId;
        $fee = \App\Models\FeeComponent::findOrFail($feeId);
        $this->editingFeeId = $fee->id;
        $this->fee_account_id = $fee->account_id;
        $this->fee_name = $fee->name;
        $this->fee_base_amount = $fee->base_amount;
        $this->fee_is_variable = $fee->is_variable;
        $this->fee_currency = $fee->currency;
        $this->fee_is_active = $fee->is_active;
        $this->showFeeModal = true;
    }

    public function saveFee()
    {
        $this->validate([
            'fee_name' => 'required|string|max:255',
            'fee_account_id' => 'required',
            'fee_base_amount' => 'nullable|numeric|min:0',
            'fee_is_variable' => 'boolean',
            'fee_currency' => 'required|string|max:5',
            'fee_is_active' => 'boolean',
        ]);

        \App\Models\FeeComponent::updateOrCreate(
            ['id' => $this->editingFeeId],
            [
                'service_id' => $this->currentServiceId,
                'name' => $this->fee_name,
                'base_amount' => $this->fee_is_variable ? null : (float) $this->fee_base_amount,
                'account_id' => $this->fee_account_id,
                'is_variable' => $this->fee_is_variable,
                'currency' => $this->fee_currency,
                'is_active' => $this->fee_is_active,
            ]
        );

        $this->toast('success', $this->editingFeeId ? 'Fee updated!' : 'Fee created!');
        $this->showFeeModal = false;
        $this->resetFeeForm();
        $this->loadServices(); // Refresh list!
    }

    public function resetFeeForm()
    {
        $this->editingFeeId = null;
        $this->fee_account_id = null;
        $this->fee_name = '';
        $this->fee_base_amount = '';
        $this->fee_is_variable = false;
        $this->fee_currency = 'PHP';
        $this->fee_is_active = true;
    }

    public function loadServices()
    {
        $this->services = Service::with('feeComponents')->orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $this->serviceId = $service->id;
        $this->type = $service->type;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->is_active = $service->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        Service::updateOrCreate(
            ['id' => $this->serviceId],
            [
                'name' => $this->name,
                'type' => $this->type,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        $this->toast('success', $this->serviceId ? 'Service updated successfully!' : 'Service created successfully!');
        $this->showModal = false;
        $this->resetForm();
        $this->loadServices();
    }

//    public function delete($id)
//    {
//        $service = Service::findOrFail($id);
//        $service->delete();
//        $this->toast('success', 'Service deleted successfully!');
//        $this->loadServices();
//    }

    public function resetForm()
    {
        $this->serviceId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.services.index');
    }
}
