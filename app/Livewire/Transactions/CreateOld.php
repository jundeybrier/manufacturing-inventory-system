<?php

namespace App\Livewire\Transactions;

use App\Models\CashieringSession;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreateOld extends Component
{
    public $firstname = '';
    public $lastname = '';
    public $middlename = '';
    public $rep_name = '';
    public $services = [];

    public $totalAmount = 0.00;
    public $amount_paid = 0.00;
    public $remarks = '';
    public $or_number;
    public $reference_number;
    public $selectedServices = [];
    public $products = [];
    public $txn = null;
    public bool $printReceipt = true;
    public $activeSession;
    public bool $alreadyOpenedToday = false;

    public $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 1];
    public $counts = []; // bound to input fields
    public bool $showCloseModal = false;
    public bool $showHistoryModal = false;
    public $todayHistory = [];
    public $userId;


    public function mount()
    {
        $this->userId = auth()->id();
        $this->products = Product::where('status', 1)->get();
        $this->or_number = auth()->user()->nextOrNumber();
        if (!$this->txn) {
            $this->txn = Transaction::where('user_id', auth()->id())
                ->whereDate('date', Carbon::today())
                ->latest('date')
                ->first();
        }
        $this->printReceipt = auth()->user()->print_receipt_enabled ?? true;
        $this->activeSession = CashieringSession::where('user_id', auth()->id())
            ->whereNull('closed_at')
            ->latest()
            ->first();

        $this->alreadyOpenedToday = CashieringSession::where('user_id', auth()->id())
            ->whereDate('opened_at', now()->toDateString())
            ->exists();
    }

    public function selectProduct($productId)
    {
        $product = Product::with('services')->find($productId);

        if ($product) {
            foreach ($product->services as $service) {
                $existingIndex = collect($this->selectedServices)
                    ->search(fn($s) => $s['id'] === $service->id);

                if ($existingIndex !== false) {
                    $this->selectedServices[$existingIndex]['quantity'] += 1;
                } else {
                    $this->selectedServices[] = [
                        'id' => $service->id,
                        'name' => $service->name,
                        'amount' => $service->amount,
                        'quantity' => 1,
                    ];
                }
            }

            $this->recalculateTotal();
        }
    }

    public function openSession()
    {
        $alreadyOpenedToday = CashieringSession::where('user_id', auth()->id())
            ->whereDate('opened_at', now()->toDateString())
            ->exists();

        if ($alreadyOpenedToday) {
            $this->addError('session', 'You have already opened a register today.');
            return;
        }

        $this->activeSession = CashieringSession::create([
            'user_id' => auth()->id(),
            'opened_at' => now(),
        ]);
    }

    public function showHistory()
    {
        $userId = auth()->id();
        $this->todayHistory = Transaction::whereDate('datetime_validated', today())
            ->where('user_id', $userId)
            ->with('details')
            ->orderBy('datetime_validated', 'desc')
            ->get();

        $this->showHistoryModal = true;
    }


    public function finalizeClose()
    {
        if (!$this->activeSession) {
            $this->addError('session', 'No open session found.');
            return;
        }

        foreach ($this->counts as $i => $qty) {
            $qty = intval($qty);
            if ($qty > 0) {
                CashCollection::create([
                    'cashiering_session_id' => $this->activeSession->id,
                    'denomination' => $this->denominations[$i],
                    'quantity' => $qty,
                    'total_amount' => $this->denominations[$i] * $qty,
                ]);
            }
        }

        $this->activeSession->update([
            'closed_at' => now(),
        ]);

        $this->showCloseModal = false;
        $this->activeSession = null;
    }

    public function confirmClose()
    {
        $this->reset('counts');
        $this->showCloseModal = true;
    }

    protected function computeClosingCash(): float
    {
        // Example logic, you may refine this
        return Transaction::where('user_id', auth()->id())
            ->whereBetween('created_at', [$this->activeSession->opened_at, now()])
            ->sum('amount_paid');
    }

    function isWindowsShareReachable($path)
    {
        dd($path);
        return file_exists($path); // e.g., '\\\\PC-NAME\\Printer'
    }

    public function printReceipt(Transaction $transaction, bool $isRevalidated = false)
    {
        $printerPath = auth()->user()?->printer_path;

        if (empty($printerPath)) {
            $this->js(<<<'JS'
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                type: 'warning',
                message: 'Printer path is not set!',
            }
        }))
        JS);
            return;
        }

        $transaction->loadMissing('details');

        $ESC = chr(27);
        $CONDENSED = chr(15);
        $receipt = '';
        $receipt .= "\n\n\n\n";
        $receipt .= $CONDENSED;
        $receipt .= "            OFFICE OF CONSULAR AFFAIRS\n\n\n\n\n";
        $receipt .= now()->format('F d Y H:i') . "\n\n\n\n";
        $receipt .= "OR #: " . $transaction->or_number;

        if ($isRevalidated) {
            $receipt .= "  (REVALIDATED)";
        }
        $receipt .= "\nName: " . $transaction->fullname . "\n";
        $receipt .= "\nServices:\n";

        foreach ($transaction->details as $detail) {
            $receipt .= "{$detail->quantity}  {$detail->name}  {$detail->price}\n";
        }

        $receipt .= "----------------------\n";
        $receipt .= "TOTAL: ₱" . number_format($transaction->amount_paid, 2) . "\n\n\n";
        $receipt .= chr(18);
        $receipt .= chr(12); // eject paper

        $os = PHP_OS_FAMILY;
        if ($os === 'Windows') {
            // Windows Printing
            $tempFile = 'C:\\print\\receipt.txt';
            if (!file_exists('C:\\print')) {
                mkdir('C:\\print', 0777, true);
            }
            file_put_contents($tempFile, $receipt);

            $printerPath = '\\\\' . ltrim($printerPath, '\\'); // normalize path
            exec("copy " . escapeshellarg($tempFile) . " \"$printerPath\"");
        } else {
            // Linux / macOS Printing
            $tempFile = '/tmp/receipt.txt';
            file_put_contents($tempFile, $receipt);

            // Use raw printing to preserve ESC/POS formatting
            exec("lp -d " . escapeshellarg($printerPath) . " -o raw " . escapeshellarg($tempFile));
        }
    }

    public function revalidate($transactionId)
    {
        $transaction = Transaction::with('details')->findOrFail($transactionId);

        $this->printReceipt($transaction, true);

        $this->js(<<<'JS'
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                type: 'success',
                message: 'Receipt revalidated and reprinted.'
            }
        }));
    JS);
    }

    public function recalculateTotal()
    {
        $this->totalAmount = collect($this->selectedServices)
            ->sum(fn($s) => $s['amount'] * $s['quantity']);
    }

    public function removeSelectedService($id)
    {
        $this->selectedServices = collect($this->selectedServices)
            ->reject(fn($s) => $s['id'] === $id)
            ->values()
            ->toArray();

        $this->totalAmount = collect($this->selectedServices)->sum('amount');
    }

    public function getTotalCollectionsProperty()
    {


        return Transaction::whereDate('datetime_validated', today())
            ->where('user_id', $this->userId)
            ->with('details')
            ->get()
            ->sum(function ($transaction) {
                return $transaction->details->sum('total');
            });
    }

    public function getTransactionCountProperty()
    {
        return Transaction::whereDate('datetime_validated', today())
            ->where('user_id', $this->userId)
            ->whereNull('datetime_voided')
            ->count();
    }

    public function getVoidedCountProperty()
    {
        return Transaction::whereDate('date', today())
            ->whereNotNull('datetime_voided')
            ->count();
    }

    public function submitTransaction()
    {
        if (!$this->activeSession) {
            $this->addError('session', 'No active cashiering session. Please open the register.');
            return;
        }

        $this->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'or_number' => ['required', 'integer', Rule::unique('transactions', 'or_number')],
            'selectedServices' => ['required', 'array', 'min:1'],
        ]);

        $token = strtoupper(Str::random(10));

        $transaction = Transaction::create([
            'token' => $token,
            'date' => now()->toDateString(),
            'reference_number' => $this->reference_number,
            'or_number' => $this->or_number,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'middlename' => $this->middlename,
            'rep_name' => $this->rep_name,
            'amount_paid' => $this->amount_paid,
            'remarks' => $this->remarks,
            'counter' => 'default',
            'user_id' => Auth::id(),
            'datetime_created' => now(),
            'datetime_validated' => now(),
            'validated_by' => Auth::user()->name,
            'created_by' => Auth::user()->name,
        ]);

        $this->txn = $transaction;

        foreach ($this->selectedServices as $service) {
            TransactionDetail::create([
                't_id' => $transaction->id,
                'name' => $service['name'],
                'service_id' => $service['id'], // Update as needed
                'quantity' => $service['quantity'],
                'price' => $service['amount'],
                'total' => $service['quantity'] * $service['amount'],
                'conversion_rate' => 0,
                'date' => now()->toDateString(),
                'datetime_created' => now(),
                'created_by' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);
        }

        $transaction->load('details');

        $user = auth()->user();
        if ($user->print_receipt_enabled !== $this->printReceipt) {
            $user->print_receipt_enabled = $this->printReceipt;
            $user->save();
        }

        if ($this->printReceipt) {
            $this->printReceipt($transaction);
        }

        $this->reset([
            'firstname',
            'middlename',
            'lastname',
            'rep_name',
            'or_number',
            'reference_number',
            'amount_paid',
            'remarks',
            'selectedServices',
            'totalAmount',
        ]);

        $this->recalculateTotal();
        $this->or_number = auth()->user()->nextOrNumber();

        if($transaction->id){
            $this->js(<<<'JS'
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'info',
                    message: 'Transaction successfully saved!',
                }
            }))
        JS);
        }

//
//        session()->flash('status', 'Transaction created!');
//        return redirect()->route('transactions.index');
    }

    public function render()
    {
        $printerPath = auth()->user()->printer_path;
        return view('livewire.transactions.create', compact('printerPath'));
    }
}
