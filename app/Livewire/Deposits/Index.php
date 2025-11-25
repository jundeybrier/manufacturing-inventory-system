<?php

namespace App\Livewire\Deposits;

use App\Models\Deposit;
use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $showForm = false;
    public $search = '';
    public $perPage = 10;

    public $date;
    public $fund_source_id;
    public $amount;
    public $reference_number;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch($value)
    {
        $this->search = $value;
    }

    public function render()
    {
        $deposits = Deposit::where('office_id', Auth::user()->office_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $search = '%' . $this->search . '%';
                    $q->where('reference_number', 'like', $search)
                        ->orWhere('amount', 'like', $search)
                        ->orWhereHas('fundSource', function ($fs) use ($search) {
                            $fs->where('name', 'like', $search);
                        });
                });
            })
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);

        $fundSources = Account::orderBy('name')->get();

        return view('livewire.deposits.index', compact('deposits', 'fundSources'))
            ->title('Deposits');
    }
}
