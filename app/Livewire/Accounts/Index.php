<?php

namespace App\Livewire\Accounts;

use App\Livewire\BaseComponent;
use App\Models\Account;
use Livewire\Component;

class Index extends BaseComponent
{
    public $accounts;
    public $showModal = false;
    public $accountId;
    public $name = '';
    public $code = '';
    public $description = '';
    public $is_active = true;

    public function mount()
    {
        $this->loadAccounts();
    }

    public function loadAccounts()
    {
        $this->accounts = Account::orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $this->accountId = $account->id;
        $this->name = $account->name;
        $this->code = $account->code;
        $this->description = $account->description;
        $this->is_active = $account->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        Account::updateOrCreate(
            ['id' => $this->accountId],
            [
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        $this->toast('success', $this->accountId ? 'Account updated successfully!' : 'Account created successfully!');
        $this->showModal = false;
        $this->resetForm();
        $this->loadAccounts();
    }

    public function delete($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();
        $this->toast('success', 'Account deleted successfully!');
        $this->loadAccounts();
    }

    public function resetForm()
    {
        $this->accountId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.accounts.index');
    }
}

