<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class TransactionReportController extends Controller
{
    public function daily(Request $request)
    {
        $user = auth()->user();
        $date = today();
        // Example: adjust these queries to your schema
        $transactions = Transaction::with([
            'details.account',            // 👈 this is the key part
            'details.service',
            'details.feeComponent.account', // 👈 also include this for fallback
        ])
            ->whereDate('created_at', $date)
            ->whereNull('voided_at')
            ->where('user_id', auth()->id())
            ->get();
        $feeComponents = \App\Models\FeeComponent::with('service')
            ->get();
        $groupedFeeComponents = $feeComponents->groupBy('name');



        $fundSources = Account::all(); // or an array, e.g. [['fund_source' => 'GAA', 'code' => 'GAA']]
        $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 1, 0.01];

        // Optionally, build transactionDetails array (for "Detailed Daily Transactions" part)
//        $transactionDetails = TransactionDetail::whereHas('transaction', function($q) use ($user, $date) {
//            $q->whereDate('created_at', $date)
//                ->where('user_id', $user->id)
//                ->whereNull('voided_at');
//        })
//            ->with('transaction')
//            ->get();
        $types = ['PASSPORT','AUTHENTICATION','NOTARIALS','OTHERS'];
        $grouped = [];

        $date = today(); // or your selected report date
        foreach ($types as $type) {
            $rows = [];

            foreach ($groupedFeeComponents as $name => $components) {
                $quantity = 0;
                $amount = 0;
                $converted = 0;

                foreach ($components as $fc) {
                    if (!$fc->service || strtoupper($fc->service->type) !== $type) {
                        continue;
                    }

                    // Get matching transaction details for this fee_component
                    $details = \App\Models\TransactionDetail::where('fee_component_id', $fc->id)
                        ->whereHas('transaction', fn ($q) =>
                        $q->whereDate('created_at', $date)
                            ->whereNull('voided_at')
                        )
                        ->get();

                    if ($details->isEmpty()) continue;

                    $qty = $details->sum('quantity');
                    $totalAmount = $details->sum('amount');
                    $unitPrice = $details->first()->amount;
                    $rate = $details->first()->exchange_rate ?? 1;
                    $isUSD = $fc->currency === 'USD';
                    $lineAmount = $unitPrice * $qty;
                    $lineConverted = $isUSD ? $lineAmount * $rate : $lineAmount;

                    $quantity += $qty;
                    $amount += $lineAmount;
                    $converted += $lineConverted;
                }

                if ($quantity === 0) continue;

                $rows[] = [
                    'component' => $name,
                    'unit_price' => $quantity > 0 ? $amount / $quantity : 0,
                    'work_units' => $quantity,
                    'amount' => $amount,
                    'rate' => $rate>1?$rate:null, // optional: you can average or leave null when grouped
                    'currency' => 'PHP', // Since everything is converted, use PHP
                    'converted' => $converted,
                ];
            }


            if (!empty($rows)) {
                $grouped[$type] = $rows;
            }
        }

        $accounts = Account::all()->keyBy('id');
        $accountSummaryTable = [];

// Initialize structure
        foreach ($types as $type) {
            $accountSummaryTable[$type] = [];
        }

// Loop through components
        foreach ($groupedFeeComponents as $name => $components) {
            foreach ($components as $fc) {
                if (!$fc->service || !$fc->account_id) continue;

                $type = strtoupper($fc->service->type);
                if (!in_array($type, $types)) continue;

                $accountName = $accounts[$fc->account_id]->name ?? 'Unknown';

                $details = TransactionDetail::where('fee_component_id', $fc->id)
                    ->whereHas('transaction', fn ($q) =>
                    $q->whereDate('created_at', $date)
                        ->where('user_id', auth()->id())
                        ->whereNull('voided_at')
                    )->get();

                if ($details->isEmpty()) continue;

                $qty = $details->sum('quantity');
                $unitPrice = $details->first()->amount;
                $rate = $details->first()->exchange_rate ?? 1;
                $isUSD = $fc->currency === 'USD';
                $lineAmount = $unitPrice * $qty;
                $converted = $isUSD ? $lineAmount * $rate : $lineAmount;

                // Add to matrix
                if (!isset($accountSummaryTable[$type][$accountName])) {
                    $accountSummaryTable[$type][$accountName] = 0;
                }

                $accountSummaryTable[$type][$accountName] += $converted;
            }
        }


        $grandTotal = collect($grouped)->flatten(1)->sum('converted');


        $pdf = Pdf::loadView('transactions.report-pdf', compact(
            'transactions', 'user', 'date', 'fundSources', 'denominations', 'grouped', 'grandTotal', 'accountSummaryTable'
        ));

        return $pdf->stream('daily-collections-report.pdf');
    }
}
