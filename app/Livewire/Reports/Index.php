<?php

namespace App\Livewire\Reports;

use Carbon\Carbon;
use Livewire\Component;
use App\Livewire\BaseComponent;

class Index extends BaseComponent
{
    public $date;
    public $userId;
    public $officeUsers = [];
    public $canSelectUser = false;

    public $crrStartDate;
    public $crrEndDate;
    public $crrUserId;
    public $crrAccountId = 1;
    public $accounts;
    public $format = 'pdf';

    public function mount()
    {
        $this->date = today()->format('Y-m-d');
        $authUser = auth()->user();

        // Admin: Access to ALL users in ALL sites
        if ($authUser->hasRole('admin')) {
            $this->canSelectUser = true;
            $this->officeUsers = \App\Models\User::orderBy('name')->get();
            $this->userId = $authUser->id;
            return;
        }

        // Supervisor: Access only to users in their own site
        if ($authUser->hasRole('supervisor') || $authUser->can('view office reports')) {
            $this->canSelectUser = true;
            $this->officeUsers = \App\Models\User::where('office_id', $authUser->office_id)
                ->orderBy('name')
                ->get();

            $this->userId = $authUser->id;
            return;
        }

        $this->canSelectUser = false;
        $this->officeUsers = [$authUser];
        $this->userId = $authUser->id;

        $this->accounts = \App\Models\Account::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.reports.index');
    }


    public function generate()
    {
        return redirect()->route('reports.daily', [
            'date' => $this->date,
            'user' => $this->userId
        ]);
    }

    public function generateCrr()
    {
        if (!$this->crrStartDate || !$this->crrEndDate || !$this->crrAccountId) {
            $this->toast('warning', 'Please select a complete date range.');
            return;
        }

        $userId = $this->crrUserId ?: auth()->id();

        return redirect()->route('reports.crr', [
            'start'   => $this->crrStartDate,
            'end'     => $this->crrEndDate,
            'user'    => $userId,
            'account' => $this->crrAccountId,
            'format' => $this->format,
        ]);
    }

}
