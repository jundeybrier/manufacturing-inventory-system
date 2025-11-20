<?php

namespace App\Services\Reports;

use App\Models\Account;
use App\Models\FeeComponent;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyReportService
{
    private array $types = ['PASSPORT', 'AUTHENTICATION', 'NOTARIALS', 'OTHERS'];

    public function generate(Carbon $date, $userId): array
    {
        $user = \App\Models\User::findOrFail($userId);

        $transactions = Transaction::with([
            'details.feeComponent.service',
            'details.feeComponent.account',
        ])
            ->whereDate('created_at', $date)
            ->whereNull('voided_at')
            ->where('user_id', $user->id)
            ->get();

        $allDetails = TransactionDetail::with([
            'feeComponent.service',
            'feeComponent.account'
        ])
            ->whereHas('transaction', function ($q) use ($date, $user) {
                $q->whereDate('created_at', $date)
                    ->whereNull('voided_at')
                    ->where('user_id', $user->id);
            })
            ->get();

        $grouped = $this->buildSummaryByComponent($allDetails);
        $accountSummaryTable = $this->buildSummaryByAccount($allDetails);

        return [
            'date' => $date,
            'user' => $user,
            'transactions' => $transactions,
            'grouped' => $grouped,
            'accountSummaryTable' => $accountSummaryTable,
            'fundSources' => Account::all(),
            'denominations' => [1000, 500, 200, 100, 50, 20, 10, 5, 1, 0.01],
            'grandTotal' => collect($grouped)->flatten(1)->sum('converted')
        ];
    }

    private function buildSummaryByComponent(Collection $details): array
    {
        $grouped = [];

        foreach ($this->types as $type) {
            $rows = [];

            $byComponent = $details->filter(function ($d) use ($type) {
                return strtoupper($d->feeComponent->service->type ?? '') === $type;
            })->groupBy('fee_component_id');

            foreach ($byComponent as $fcDetails) {
                $fc = $fcDetails->first()->feeComponent;
                $qty = $fcDetails->sum('quantity');
                $unit = $fcDetails->first()->amount;
                $rate = $fcDetails->first()->exchange_rate ?? 1;
                $amount = $qty * $unit;
                $converted = $fc->currency === 'USD' ? $amount * $rate : $amount;

                $rows[] = [
                    'component' => $fc->name,
                    'unit_price' => $unit,
                    'work_units' => $qty,
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'converted' => $converted,
                ];
            }

            if ($rows) {
                $grouped[$type] = $rows;
            }
        }

        return $grouped;
    }

    private function buildSummaryByAccount(Collection $details): array
    {
        $table = [];

        foreach ($this->types as $type) {
            $table[$type] = [];
        }

        $details->each(function ($d) use (&$table) {
            $type = strtoupper($d->feeComponent->service->type ?? '');
            if (!in_array($type, $this->types)) return;

            $account = $d->feeComponent->account->name ?? 'Unknown';
            $rate = $d->exchange_rate ?? 1;
            $amount = $d->quantity * $d->amount;
            $converted = $d->feeComponent->currency === 'USD' ? $amount * $rate : $amount;

            if (!isset($table[$type][$account])) {
                $table[$type][$account] = 0;
            }

            $table[$type][$account] += $converted;
        });

        return $table;
    }
}
