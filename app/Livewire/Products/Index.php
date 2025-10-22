<?php

namespace App\Livewire\Products;

use App\Livewire\BaseComponent;
use App\Models\Product;
use App\Models\Service;
use Livewire\Component;

class Index extends BaseComponent
{
    public $products;
    public $services;
    public $showModal = false;
    public $showServiceModal = false;

    public $productId = null;

    public $name = '';
    public $description = '';
    public $is_active = true;
    public $selectedServiceIds = [];

    // new service modal fields
    public $service_name = '';
    public $service_type = '';
    public $service_description = '';
    public $service_is_active = true;

    public function mount()
    {
        $this->loadProducts();
        $this->loadServices();
    }

    public function loadProducts()
    {
        $this->products = Product::with(['services.feeComponents'])
            ->orderBy('name')->get();
    }

    public function loadServices()
    {
        $this->services = Service::where('is_active', true)
            ->with('feeComponents')
            ->orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $product = Product::with('services')->findOrFail($id);

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->is_active = $product->is_active;
        $this->selectedServiceIds = $product->services->pluck('id')->toArray();

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $product = Product::updateOrCreate(
            ['id' => $this->productId],
            [
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        $product->services()->sync($this->selectedServiceIds);

        $this->toast('success', $this->productId ? 'Product updated successfully!' : 'Product created successfully!');
        $this->showModal = false;
        $this->resetForm();
        $this->loadProducts();
    }

    /** ─────────────── NEW SERVICE CREATION ─────────────── */

    public function openAddServiceModal()
    {
        $this->resetServiceForm();
        $this->showServiceModal = true;
    }

    public function saveService()
    {
        $this->validate([
            'service_name' => 'required|string|max:255',
            'service_type' => 'required|string|max:255',
            'service_description' => 'nullable|string|max:255',
        ]);

        $service = Service::create([
            'name' => $this->service_name,
            'type' => $this->service_type,
            'description' => $this->service_description,
            'is_active' => $this->service_is_active,
        ]);

        // add to selected list immediately
        $this->selectedServiceIds[] = $service->id;

        $this->toast('success', 'Service created successfully!');
        $this->showServiceModal = false;
        $this->resetServiceForm();
        $this->loadServices(); // refresh dropdown list
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->selectedServiceIds = [];
    }

    public function resetServiceForm()
    {
        $this->service_name = '';
        $this->service_type = '';
        $this->service_description = '';
        $this->service_is_active = true;
    }

    public function render()
    {
        return view('livewire.products.index');
    }
}
