<?php

namespace App\Livewire\Transactions;

use App\Livewire\BaseComponent;
use App\Models\CashierSession;
use App\Models\Service;
use App\Models\SessionDenomination;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Create extends BaseComponent
{
    public $reference = '';
    public $firstname = '';
    public $lastname = '';
    public $middlename = '';
    public $rep_name = '';
    public $remarks = '';
    public $or_number;
    public $reference_number;

    public $services = [];
    public $selectedFees = [];

    public $totalAmount = 0.00;
    public $amount_paid = 0.00;
    public $txn = null;
    public bool $printReceipt = true;
    public $activeSession;
    public bool $alreadyOpenedToday = false;

    public $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 1, 0.01];
    public $counts = [];
    public $denominationsTotal = 0;
    public bool $showCloseModal = false;
    public bool $showHistoryModal = false;
    public $todayHistory = [];
    public $userId;

    public $showVariableModal = false;
    public $modalService = null; // Will hold the selected Service model
    public $variableAmounts = []; // [fee_component_id => value]
    public $usdConversionRate = null;
    public bool $showConfirmModal = false;

// This method is triggered by the button
    public function confirmSubmit()
    {
        if($this->activeSession && !$this->alreadyOpenedToday) {
            $this->addError('session', 'No active cashiering session. Please open the register.');
            return;
        }

        if(count($this->selectedFees) == 0) {
            $this->addError('session', 'Please select a service.');
            return;
        }

        $this->validate([
            'firstname'     => 'required',
            'lastname'      => 'required',
            'or_number'     => ['required', Rule::unique('transactions', 'or_number')],
            'selectedFees'  => ['required', 'array', 'min:1'],
        ]);
        $this->showConfirmModal = true;
    }

    public function mount()
    {
        $this->userId = auth()->id();
        $this->services = Service::with([
            'feeComponents' => function ($q) {
                $q->where('is_active', 1)->with('account');
            }
        ])->where('is_active', 1)->get();

        $this->or_number = auth()->user()->nextOrNumber();

        if (!$this->txn) {
            $this->txn = Transaction::where('user_id', auth()->id())
                ->whereDate('created_at', Carbon::today())
                ->latest('created_at')
                ->first();
        }

        $this->printReceipt = auth()->user()->print_receipt_enabled ?? true;
        $this->activeSession = CashierSession::where('user_id', auth()->id())
            ->whereNull('closed_at')
            ->latest()
            ->first();

        $this->alreadyOpenedToday = CashierSession::where('user_id', auth()->id())
            ->whereDate('opened_at', now()->toDateString())
            ->exists();
    }

    public function selectService($serviceId)
    {
        // Find the service from the loaded collection
        $service = $this->services->where('id', $serviceId)->first();
        if (!$service) return;

        // Check if any fee is variable or USD
        $hasVariable = false;
        $hasDollar = false;
        foreach ($service->feeComponents as $fee) {
            if ($fee->is_variable) $hasVariable = true;
            if ($fee->currency === 'USD') $hasDollar = true;
        }

        // If variable/dollar, open modal and don't add anything yet
        if ($hasVariable || $hasDollar) {
            $this->modalService = $service;
            $this->variableAmounts = [];
            foreach ($service->feeComponents as $fee) {
                $this->variableAmounts[$fee->id] = $fee->is_variable || $fee->currency === 'USD'
                    ? null
                    : $fee->base_amount;
            }
            $this->usdConversionRate = null;
            $this->showVariableModal = true;
            return;
        }

        // Only add here if there are NO variable/dollar fees
        foreach ($service->feeComponents as $fee) {
            $existingIndex = collect($this->selectedFees)
                ->search(fn($f) => $f['fee_id'] === $fee->id);

            if ($existingIndex !== false) {
                $this->selectedFees[$existingIndex]['quantity'] += 1;
            } else {
                $this->selectedFees[] = [
                    'fee_id'       => $fee->id,
                    'fee_name'     => $fee->name,
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'quantity'     => 1,
                    'price'        => $fee->base_amount,
                    'currency'     => $fee->currency,
                ];
            }
        }
        $this->recalculateTotal();
    }

    public function closeVariableModal()
    {
        $this->showVariableModal = false;
        $this->modalService = null;
        $this->variableAmounts = [];
        $this->usdConversionRate = null;
    }


    public function applyVariableFees()
    {
        if (!$this->modalService) return;

        $service = $this->modalService;

        foreach ($service->feeComponents as $fee) {
            $amount = $fee->base_amount;

            $dynamicServiceName = $service->name;
            if ($fee->currency === 'USD' && $this->usdConversionRate) {
                $dynamicServiceName .= " (@ ₱" . number_format($this->usdConversionRate, 2) . " per $)";
            }

            if ($fee->is_variable || $fee->currency === 'USD') {
                $inputAmount = $this->variableAmounts[$fee->id] ?? null;
                if ($fee->currency === 'USD') {
                    $rate = $this->usdConversionRate ?: 1;
                    $amount = floatval($inputAmount) * floatval($rate);
                } else {
                    $amount = floatval($inputAmount);
                }
            }

            $this->selectedFees[] = [
                'fee_id'       => $fee->id,
                'fee_name'     => $fee->name,
                'service_id'   => $service->id,
                'service_name' => $dynamicServiceName,
                'quantity'     => 1,
                'price'        => $inputAmount,
                'exchange_rate'=> $rate,
                'currency'     => $fee->currency, // always PHP for receipt/report
            ];
        }

        $this->showVariableModal = false;
        $this->modalService = null;
        $this->variableAmounts = [];
        //$this->usdConversionRate = null;
        $this->recalculateTotal();
    }


    public function removeSelectedFee($feeId)
    {
        $this->selectedFees = collect($this->selectedFees)
            ->reject(fn($f) => $f['fee_id'] === $feeId)
            ->values()
            ->toArray();

        $this->recalculateTotal();
    }

    public function recalculateTotal()
    {
        $this->totalAmount = collect($this->selectedFees)
            ->sum(fn($f) => $f['price'] * $f['quantity']);
    }

    public function submitTransaction()
    {
        $this->showConfirmModal = false;
        if (!$this->activeSession) {
            $this->addError('session', 'No active cashiering session. Please open the register.');
            return;
        }elseif($this->activeSession && !$this->alreadyOpenedToday) {
            $this->addError('session', 'No active cashiering session. Please open the register.');
            return;
        }

        $this->validate([
            'firstname'     => 'required',
            'lastname'      => 'required',
            'or_number'     => ['required', Rule::unique('transactions', 'or_number')],
            'selectedFees'  => ['required', 'array', 'min:1'],
        ]);

        // Compute total (if not precomputed)
        $totalAmount = collect($this->selectedFees)->sum(function ($fee) {
            return floatval($fee['price']) * intval($fee['quantity']);
        });

        $transaction = Transaction::create([
            'or_number'      => $this->or_number,
            'user_id'        => Auth::id(),
            'office_id'      => Auth::user()->office_id ?? null,
            'session_id'     => $this->activeSession->id ?? null,

            'firstname'      => $this->firstname,
            'middlename'     => $this->middlename,
            'lastname'       => $this->lastname,
            'rep_name'       => $this->rep_name,

            'customer_name'  => trim(
                $this->firstname . ' ' .
                ($this->middlename ? $this->middlename . ' ' : '') .
                $this->lastname
            ),

            'remarks'        => $this->remarks,
            'total_amount'   => $totalAmount,
            'currency'       => 'PHP', // Or $this->currency if supporting multiple
            'exchange_rate'  => null,  // Or user input if using dollars
            'status'         => 'completed',
            'voided_by'      => null,
            'voided_at'      => null,
            'synced_at'      => null,
        ]);

        $this->txn = $transaction;

        foreach ($this->selectedFees as $fee) {
            if (empty($fee['price']) || empty($fee['quantity'])) continue;

            TransactionDetail::create([
                'transaction_id'   => $transaction->id,
                'fee_component_id' => $fee['fee_id'],
                'service_id'       => $fee['service_id'] ?? null,
                'account_id'       => $fee['account_id'] ?? null,
                'description'      => $fee['fee_name'] ?? null,
                'quantity'         => $fee['quantity'],
                'amount'           => $fee['price'],
                'exchange_rate'    => $this->usdConversionRate,
                'total'            => $fee['price'] * $fee['quantity'],
                'currency'         => $fee['currency'] ?? 'PHP',
                'synced_at'        => null,
            ]);
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
            'selectedFees',
            'totalAmount',
        ]);
        $this->recalculateTotal();
        $this->or_number = auth()->user()->nextOrNumber();

        if($transaction->id){
            $this->toast('success', 'Transaction successfully saved!');
        }
    }

    public function printReceipt(Transaction $transaction, bool $isRevalidated = false)
    {
        $user = auth()->user();
        $printerPath = $user?->printer_path;

        if (empty($printerPath)) {
            $this->toast('warning', 'Printer path is not set!');
            return;
        }

        // Always eager load needed relationships
        $transaction->loadMissing('details');

        $ESC = chr(27);
        $CONDENSED = chr(15);
        $receipt = '';

        // Receipt Header
        $receipt .= "\n\n\n\n";
        $receipt .= $CONDENSED;
        $receipt .= "            OFFICE OF CONSULAR AFFAIRS\n";
        $receipt .= "           Cashiering Receipt\n\n";
        $receipt .= now()->format('F d Y H:i') . "\n";
        $receipt .= "OR #: " . $transaction->or_number;
        if ($isRevalidated) {
            $receipt .= "  (REVALIDATED)";
        }
        $receipt .= "\nName: " . ($transaction->fullname ?? '-') . "\n";

        // Service details
        $receipt .= "\nServices:\n";
        foreach ($transaction->details as $detail) {
            $receipt .= sprintf(
                "%2s  %-20s %8s\n",
                $detail->quantity,
                mb_strimwidth($detail->name, 0, 20, '…'),
                number_format($detail->price, 2)
            );
        }

        $receipt .= "-------------------------------\n";
        $receipt .= "TOTAL: ₱" . number_format($transaction->amount_paid, 2) . "\n";
        $receipt .= "\n\n";

        // Add cut/eject
        $receipt .= chr(18); // select print mode
        $receipt .= chr(12); // form feed (eject)

        $os = PHP_OS_FAMILY;
        if ($os === 'Windows') {
            // --- Windows Printing
            $tempDir = 'C:\\print';
            $tempFile = $tempDir . '\\receipt.txt';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            file_put_contents($tempFile, $receipt);

            // Normalize printer path (share or local)
            $normalizedPath = (str_starts_with($printerPath, '\\\\') ? $printerPath : '\\\\' . ltrim($printerPath, '\\'));

            // Use copy command to send to printer share
            exec("copy " . escapeshellarg($tempFile) . " " . escapeshellarg($normalizedPath), $output, $result);

            if ($result !== 0) {
                $this->toast('error', 'Failed to print receipt on Windows. Please check printer path and permissions.');
            }
        } else {
            // --- Linux/macOS Printing
            $tempFile = '/tmp/receipt.txt';
            file_put_contents($tempFile, $receipt);

            // Use raw to preserve ESC codes
            exec("lp -d " . escapeshellarg($printerPath) . " -o raw " . escapeshellarg($tempFile), $output, $result);

            if ($result !== 0) {
                $this->toast('error', 'Failed to print receipt on Linux. Please check printer path and permissions.');
            }
        }
    }

    public function revalidate($transactionId)
    {
        $transaction = Transaction::with('details')->findOrFail($transactionId);

        $this->printReceipt($transaction, true);

        $this->toast('success', 'Revalidation successful!');
    }





//    public function getTotalCollectionsProperty()
//    {
//
//
//        return Transaction::whereDate('created_at', today())
//            ->where('user_id', $this->userId)
//            ->with('details')
//            ->get()
//            ->sum(function ($transaction) {
//                return $transaction->details->sum('total');
//            });
//    }
//
//    public function getTransactionCountProperty()
//    {
//        return Transaction::whereDate('created_at', today())
//            ->where('user_id', $this->userId)
//            ->whereNull('voided_at')
//            ->count();
//    }
//
//    public function getVoidedCountProperty()
//    {
//        return Transaction::whereDate('created_at', today())
//            ->where('user_id', $this->userId)
//            ->whereNotNull('voided_at')
//            ->count();
//    }

    public function openSession()
    {
        $user = auth()->user();

        // Check for office assignment
        if (empty($user->office_id)) {
            $this->addError('office', 'You do not have an assigned office. Please contact your administrator.');
            return;
        }
        if ($this->activeSession) {
            $this->toast('warning', 'You have an unclosed session that needs to be closed first.');
            return;
        }


        $alreadyOpenedToday = \App\Models\CashierSession::where('user_id', $user->id)
            ->whereDate('opened_at', now()->toDateString())
            ->exists();

        if ($alreadyOpenedToday) {
            $this->addError('session', 'You have already opened a register today.');
            return;
        }

        $this->activeSession = \App\Models\CashierSession::create([
            'user_id'   => $user->id,
            'office_id' => $user->office_id,
            'opened_at' => now(),
            // uuid auto-set
        ]);

        $this->alreadyOpenedToday = true;
    }

    public function showHistory()
    {
        $userId = auth()->id();
        $this->todayHistory = Transaction::whereDate('created_at', \Carbon\Carbon::parse($this->activeSession->opened_at)->toDateString())
            ->where('user_id', $userId)
            ->with('details')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->showHistoryModal = true;
    }

    public function finalizeClose()
    {
        if (!$this->activeSession) {
            $this->addError('session', 'No open session found.');
            return;
        }

        // Compute current denominations total
        $denominationTotal = $this->denominationsTotal;
        $expectedTotal = $this->activeSession->total_collections;

        if (bccomp($denominationTotal, $expectedTotal, 2) !== 0) {
            $this->addError('denomination_total', 'Denomination total (₱' . number_format($denominationTotal, 2) . ') does not match the expected total collections (₱' . number_format($expectedTotal, 2) . ').');
            return;
        }

        foreach ($this->counts as $i => $qty) {
            $qty = intval($qty);
            if ($qty > 0) {
                SessionDenomination::create([
                    'session_id' => $this->activeSession->id,
                    'denomination' => $this->denominations[$i],
                    'quantity' => $qty,
                    'total' => $this->denominations[$i] * $qty,
                ]);
            }
        }

        $this->activeSession->update([
            'closed_at' => now(),
        ]);

        $this->showCloseModal = false;
        $this->activeSession = null;
    }

    public function computeDenominationsTotal()
    {
        $total = 0;
        foreach ($this->counts as $i => $qty) {
            $total += $this->denominations[$i] * intval($qty);
        }
        $this->denominationsTotal = $total;
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

    public function render()
    {
        $printerPath = auth()->user()->printer_path;
        return view('livewire.transactions.create', compact('printerPath'));
    }
}

