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

    // Form fields
    public $date;
    public $fund_source_id;
    public $amount;
    public $reference_number;

    protected function rules()
    {
        return [
            'date' => ['required', 'date'],
            'fund_source_id' => ['required', Rule::exists('accounts', 'id')],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['required', 'string', 'max:255'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch($value)
    {
        $this->search = $value;
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        Deposit::create([
            'office_id' => Auth::user()->office_id,
            'user_id' => Auth::id(),
            'date' => $this->date,
            'fund_source_id' => $this->fund_source_id,
            'amount' => $this->amount,
            'reference_number' => $this->reference_number,
        ]);

        $this->resetForm();
        $this->showForm = false;

        session()->flash('success', 'Deposit added successfully.');
    }

    private function resetForm()
    {
        $this->date = now()->toDateString();
        $this->fund_source_id = '';
        $this->amount = '';
        $this->reference_number = '';
    }

    public function render()
    {
        $search = '%' . $this->search . '%';

        $deposits = Deposit::where('office_id', Auth::user()->office_id)
            ->when($this->search, function ($query) use ($search) {
                $query->where('reference_number', 'like', $search)
                    ->orWhere('amount', 'like', $search)
                    ->orWhereHas('fundSource', fn($fs) =>
                    $fs->where('name', 'like', $search)
                    );
            })
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);

        $fundSources = Account::orderBy('name')->get();

        return view('livewire.deposits.index', compact('deposits', 'fundSources'))
            ->title('Deposits');
    }
}
