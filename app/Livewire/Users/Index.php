<?php

namespace App\Livewire\Users;

use App\Livewire\BaseComponent;
use App\Models\User;
use App\Models\Office;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class Index extends BaseComponent
{
    public $users;
    public $roles;
    public $offices;
    public $showModal = false;

    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = '';
    public $office_id = '';

    public function mount()
    {
        $this->loadUsers();
        $this->roles = Role::pluck('name')->toArray();
        $this->offices = Office::orderBy('name')->get();
    }

    public function loadUsers()
    {
        $this->users = User::with('roles', 'office')->orderBy('name')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->office_id = $user->office_id;
        $this->role = $user->roles->pluck('name')->first() ?? '';
        $this->password = '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'nullable|string',
            'office_id' => 'required|exists:offices,id',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'office_id' => $this->office_id,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $data);
        $user->syncRoles([$this->role]);

        $this->toast('success', $this->userId ? 'User updated successfully!' : 'User created successfully!');

        $this->showModal = false;
        $this->resetForm();
        $this->loadUsers();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->toast('warning', 'You cannot delete your own account!');
            return;
        }

        $user->delete();
        $this->toast('success', 'User deleted successfully!');
        $this->loadUsers();
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->office_id = '';
        $this->password = '';
        $this->role = '';
    }

    public function render()
    {
        return view('livewire.users.index');
    }
}
