<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserPreferences extends Component
{
    public $preferences = [];

    public function mount()
    {
        $this->preferences = auth()->user()->pref('report_roles', [
            'consular_supervisor' => [
                'name' => '',
                'designation' => 'Consular Supervisor',
            ],
            'administrative_officer' => [
                'name' => '',
                'designation' => 'Administrative Officer',
            ],
            'head_of_consular_office' => [
                'name' => '',
                'designation' => 'Head of Consular Office',
            ],
        ]);
    }

    public function save()
    {
        $user = auth()->user();
        $settings = $user->preferences?->settings ?? [];

        $settings['report_roles'] = $this->preferences;

        $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            ['settings' => $settings]
        );

        $this->dispatch('notify', 'Preferences updated!');
    }

    public function render()
    {
        return view('livewire.user-preferences');
    }
}
