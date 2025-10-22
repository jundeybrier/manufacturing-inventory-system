<?php

namespace App\Livewire\Transactions;

use App\Livewire\BaseComponent;
use App\Models\CashierSession;
use App\Models\Product;
use App\Models\Service;
use App\Models\SessionDenomination;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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

    public $products = [];
    public $selectedFees = [];
    public $selectedServices = [];

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
    public $isExchangeRateLocked = false;
    public bool $showConfirmModal = false;
    public $isRevalidated = false;

    public $modalProduct = null;

    public $showVoidModal = false;
    public $selectedTransaction = null;
    public $void_reason = '';

// This method is triggered by the button
    public function confirmSubmit()
    {
        // Check for valid session
        if (!$this->activeSession || !$this->alreadyOpenedToday) {
            $this->addError('session', 'No active cashiering session. Please open the register.');
            return;
        }

        // Check if at least one service was selected
        if (count($this->selectedServices) === 0) {
            $this->addError('session', 'Please select at least one service.');
            return;
        }

        // Validate basic inputs
        $this->validate([
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'required|string|max:255',
            'or_number'        => ['required', 'integer', Rule::unique('transactions', 'or_number')],
            'selectedServices' => ['required', 'array', 'min:1'],
        ]);

        // ✅ Everything is valid — show confirmation modal
        $this->showConfirmModal = true;
    }


    public function mount()
    {
        $this->userId = auth()->id();

        // Load products with their services and fee components
        $this->products = Product::with([
            'services.feeComponents' => function ($q) {
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

    public function selectProduct($productId)
    {
        // ✅ Load product with all active services + fee components
        $product = \App\Models\Product::with([
            'services.feeComponents' => function ($q) {
                $q->where('is_active', true);
            }
        ])->find($productId);

        if (!$product) return;

        $hasVariable = false;
        $this->modalProduct = null;
        $this->variableAmounts = [];

        foreach ($product->services as $service) {
            // Compute total for fixed PHP fees only
            $fixedTotal = $service->feeComponents
                ->where('is_variable', false)
                ->sum('base_amount');

            $serviceHasVariable = $service->feeComponents
                    ->where('is_variable', true)
                    ->count() > 0;

            // ✅ If this service has variable components, trigger modal
            if ($serviceHasVariable) {
                $hasVariable = true;
                $this->modalProduct = $product;

                // Pre-fill variableAmounts with null for all variable fees
                foreach ($service->feeComponents as $fee) {
                    if ($fee->is_variable) {
                        $this->variableAmounts[$fee->id] = null;
                    }
                }

                // ⚠️ Do NOT add variable services to $selectedServices yet
                continue;
            }

            // ✅ Add only fixed services immediately
            $existing = collect($this->selectedServices)
                ->firstWhere('service_id', $service->id);

            if (!$existing) {
                $this->selectedServices[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'quantity'     => 1,
                    'amount'       => $fixedTotal,
                    'has_variable' => false,
                ];
            } else {
                foreach ($this->selectedServices as &$s) {
                    if ($s['service_id'] === $service->id) {
                        $s['quantity']++;
                        break;
                    }
                }
            }
        }

        $this->recalculateTotal();

        // ✅ If any variable fee exists, prepare USD rate logic
        if ($hasVariable && $this->modalProduct) {
            // 🔒 Check if user already has a locked USD conversion rate today
            $lockedRate = \App\Models\TransactionDetail::whereHas('transaction', function ($q) {
                $q->where('user_id', auth()->id())
                    ->whereDate('created_at', now()->toDateString());
            })
                ->whereNotNull('exchange_rate')
                ->orderBy('id', 'asc')
                ->value('exchange_rate');

            if ($lockedRate) {
                $this->usdConversionRate = $lockedRate;
                $this->isExchangeRateLocked = true;
            } else {
                $this->usdConversionRate = null;
                $this->isExchangeRateLocked = false;
            }

            // ✅ Finally, show modal
            $this->showVariableModal = true;

            \Log::info('[selectProduct] Opened variable modal', [
                'product_id' => $this->modalProduct->id,
                'product_name' => $this->modalProduct->name,
                'locked_rate' => $this->usdConversionRate,
                'is_locked' => $this->isExchangeRateLocked,
                'variableAmounts' => $this->variableAmounts,
            ]);
        }
    }





    public function closeVariableModal()
    {
        $this->showVariableModal = false;
        $this->modalProduct = null; // ✅ was modalService
        $this->variableAmounts = [];
        $this->usdConversionRate = null;
    }


    public function applyVariableFees()
    {
        if (!$this->modalProduct) return;

        $product = $this->modalProduct;

        foreach ($product->services as $service) {
            foreach ($service->feeComponents->where('is_active', true) as $fee) {
                $rawInput = $this->variableAmounts[$fee->id] ?? null;

                // Skip empty fields
                if ($rawInput === null || $rawInput === '') continue;

                $rate = ($fee->currency === 'USD' && $this->usdConversionRate)
                    ? (float) $this->usdConversionRate
                    : 1;

                // Save the raw user-entered amount (USD if currency == USD)
                $amount = (float) $rawInput;

                // ✅ Update or create in selectedServices
                $existingIndex = collect($this->selectedServices)->search(fn($s) => $s['service_id'] === $service->id);

                if ($existingIndex === false) {
                    $this->selectedServices[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'service_id'   => $service->id,
                        'service_name' => $service->name,
                        'quantity'     => 1,
                        'amount'       => $amount, // keep user-entered
                        'has_variable' => true,
                        'currency'     => $fee->currency ?? 'PHP',
                    ];
                } else {
                    $this->selectedServices[$existingIndex]['amount'] = $amount;
                    $this->selectedServices[$existingIndex]['currency'] = $fee->currency ?? 'PHP';
                }
            }
        }

        $this->showVariableModal = false;
        $this->modalProduct = null;
        $this->variableAmounts = [];
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

    public function removeSelectedService($serviceId)
    {
        $this->selectedServices = collect($this->selectedServices)
            ->reject(fn($s) => $s['service_id'] === $serviceId)
            ->values()
            ->toArray();

        $this->recalculateTotal();
    }

    public function recalculateTotal()
    {
        $this->totalAmount = collect($this->selectedServices)
            ->sum(fn($s) => $s['amount'] * $s['quantity']);
    }

    public function submitTransaction()
    {
        $this->showConfirmModal = false;

        // 🔒 Validate session
        if (!$this->activeSession || !$this->alreadyOpenedToday) {
            $this->addError('session', 'No active cashiering session. Please open the register.');
            return;
        }

        // 🧩 Validate inputs
        $this->validate([
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'required|string|max:255',
            'or_number'        => ['required', 'integer', Rule::unique('transactions', 'or_number')],
            'selectedServices' => ['required', 'array', 'min:1'],
        ]);

        // 💵 Store rate for reference
        $usdRate = $this->usdConversionRate ?: null;

        // 💰 Compute total (in PHP for display only)
        $totalAmount = collect($this->selectedServices)->sum(function ($s) {
            return floatval($s['amount']) * intval($s['quantity']);
        });

        // 🧾 Create parent transaction
        $transaction = Transaction::create([
            'or_number'     => $this->or_number,
            'user_id'       => Auth::id(),
            'office_id'     => Auth::user()->office_id ?? null,
            'session_id'    => $this->activeSession->id ?? null,
            'firstname'     => $this->firstname,
            'middlename'    => $this->middlename,
            'lastname'      => $this->lastname,
            'rep_name'      => $this->rep_name,
            'customer_name' => trim($this->firstname . ' ' . ($this->middlename ? $this->middlename . ' ' : '') . $this->lastname),
            'remarks'       => $this->remarks,
            'total_amount'  => $totalAmount, // still PHP-based display
            'currency'      => 'PHP',
            'exchange_rate' => $usdRate,     // record rate of the day
            'status'        => 'completed',
        ]);

        $this->txn = $transaction;

        // 🧾 Store each fee component
        foreach ($this->selectedServices as $s) {
            $service = \App\Models\Service::with('feeComponents')->find($s['service_id']);
            if (!$service) continue;

            $components = $service->feeComponents->where('is_active', true);
            if ($components->isEmpty()) continue;

            foreach ($components as $fee) {
                $isVariable = $s['has_variable'] ?? false;
                $quantity   = intval($s['quantity']);

                // ✅ get amount properly
                if($isVariable) {
                    $enteredAmount = $s['amount'];
                }else{
                    $enteredAmount = $fee->base_amount ?? 0;
                }
                if ($enteredAmount === null || $enteredAmount === '') $enteredAmount = 0;

                $total = $enteredAmount * $quantity;

                TransactionDetail::create([
                    'transaction_id'   => $transaction->id,
                    'fee_component_id' => $fee->id,
                    'service_id'       => $service->id,
                    'account_id'       => $fee->account_id,
                    'description'      => $fee->name,
                    'quantity'         => $quantity,
                    'amount'           => $enteredAmount,
                    'total'            => $total,
                    'currency'         => $fee->currency ?? 'PHP',
                    'exchange_rate'    => $fee->currency === 'USD' ? $usdRate : null,
                ]);
            }
        }

        // 🖨️ Print receipt if enabled
        if ($this->printReceipt) {
            $this->dispatch('print-receipt', transactionId: $transaction->id);
        }

        // 🔁 Reset
        $this->reset([
            'firstname', 'middlename', 'lastname', 'rep_name',
            'reference_number', 'amount_paid', 'remarks',
            'selectedServices', 'totalAmount', 'usdConversionRate',
        ]);

        $this->recalculateTotal();
        $this->or_number = auth()->user()->nextOrNumber();

        // ✅ Success
        if ($transaction->id) {
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
        $this->isRevalidated = true;
        $transaction = Transaction::findOrFail($transactionId);

        // ✅ Dispatch Livewire v3 browser event
        $this->dispatch('print-receipt', transactionId: $transaction->id, revalidate: true);

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
        \Log::info('openSession() called', ['user_id' => $user->id ?? null]);

        // Check for office assignment
        if (empty($user->office_id)) {
            \Log::warning('User has no office_id', ['user_id' => $user->id]);
            $this->addError('office', 'You do not have an assigned office. Please contact your administrator.');
            return;
        }

        // Check for active session
        if ($this->activeSession) {
            \Log::info('User already has an active session', ['user_id' => $user->id]);
            $this->toast('warning', 'You have an unclosed session that needs to be closed first.');
            return;
        }

        // Check for existing session today
        $alreadyOpenedToday = \App\Models\CashierSession::where('user_id', $user->id)
            ->whereDate('opened_at', now()->toDateString())
            ->exists();

        \Log::info('Checked existing session for today', [
            'user_id' => $user->id,
            'alreadyOpenedToday' => $alreadyOpenedToday
        ]);

        if ($alreadyOpenedToday) {
            \Log::warning('User already opened session today', ['user_id' => $user->id]);
            $this->addError('session', 'You have already opened a register today.');
            return;
        }

        try {
            $this->activeSession = \App\Models\CashierSession::create([
                'user_id'   => $user->id,
                'office_id' => $user->office_id,
                'opened_at' => now(),
            ]);

            $this->alreadyOpenedToday = true;
            \Log::info('Cashier session created successfully', [
                'user_id' => $user->id,
                'session_id' => $this->activeSession->id ?? null
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to create cashier session', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('session', 'An error occurred while opening the session.');
        }
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

    public function confirmVoid($id)
    {
        $this->selectedTransaction = \App\Models\Transaction::find($id);
        $this->void_reason = '';
        $this->showVoidModal = true;
    }

    public function voidTransaction()
    {
        $this->validate([
            'void_reason' => 'required|string|min:5',
        ]);

        if ($this->selectedTransaction) {
            $this->selectedTransaction->update([
                'is_voided' => true,
                'void_reason' => $this->void_reason,
                'voided_by' => auth()->id(),
                'voided_at' => now(),
            ]);
        }

        $this->showVoidModal = false;
        $this->dispatch('notify', message: 'Transaction voided successfully.');
        $this->todayHistory = \App\Models\Transaction::whereDate('created_at', today())->get(); // if you have a refresh function
    }

    public function render()
    {
        $printerPath = auth()->user()->printer_path;
        return view('livewire.transactions.create', compact('printerPath'));
    }
}

