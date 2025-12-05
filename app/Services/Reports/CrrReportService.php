<?php
namespace App\Services\Reports;

use App\Models\Account;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\FeeComponent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CrrReportService
{
    public function generate(int $officeId, int $userId, int $accountId, string $start, string $end): array
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate   = Carbon::parse($end)->endOfDay();

        // Components under this account that are actually used (non-voided) in the period
        $components = FeeComponent::where('account_id', $accountId)
            ->whereHas('transactionDetails.transaction', function ($q) use ($officeId, $userId, $startDate, $endDate) {
                $q->where('office_id', $officeId)
                    ->where('user_id', $userId)
                    ->whereNull('voided_at')
                    ->where('is_voided', false)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('name')
            ->get();

        // BEGINNING RECEIPTS (before start date, non-voided, this account only)
        $beginningReceipts = Transaction::with(['details.feeComponent'])
            ->where('office_id', $officeId)
            ->where('user_id', $userId)
            ->whereNull('voided_at')
            ->where('is_voided', false)
            ->where('created_at', '<', $startDate)
            ->get()
            ->sum(function ($t) use ($accountId) {
                return $t->details->sum(function ($d) use ($accountId) {
                    $component = $d->feeComponent;
                    if (!$component || $component->account_id != $accountId) {
                        return 0;
                    }

                    // Convert USD → PHP if needed
                    return ($d->currency === 'USD' && $d->exchange_rate)
                        ? $d->total * $d->exchange_rate
                        : $d->total;
                });
            });

        // BEGINNING DEPOSITS (before start date)
        $beginningDeposits = Deposit::where('office_id', $officeId)
            ->where('user_id', $userId)
            ->where('date', '<', $startDate)
            ->sum('amount');

        $beginningBalance = $beginningReceipts - $beginningDeposits;

        // RECEIPTS in the period (non-voided)
        $receipts = Transaction::with(['details.service.feeComponents', 'user'])
            ->where('office_id', $officeId)
            ->where('user_id', $userId)
            ->whereNull('voided_at')
            ->where('is_voided', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        // DEPOSITS in the period
        $deposits = Deposit::with('user')
            ->where('office_id', $officeId)
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        // MERGE receipts + deposits into chronological entries
        $entries = collect()
            ->merge($receipts->map(function ($t) use ($components) {
                return [
                    'type'      => 'receipt',
                    'date'      => $t->created_at,
                    'or'        => $t->or_number,
                    'payor'     => trim(($t->firstname . ' ' . $t->middlename . ' ' . $t->lastname)) ?: '—',
                    'user'      => $t->user->name ?? '—',
                    'amount'    => $this->computeTotal($t),
                    'breakdown' => $this->breakdownByComponent($t, $components),
                    'details'   => $t->details,
                ];
            }))
            ->merge($deposits->map(function ($d) {
                return [
                    'type'      => 'deposit',
                    'date'      => Carbon::parse($d->date),
                    'or'        => $d->reference_number,
                    'payor'     => 'DEPOSIT OF COLLECTIONS',
                    'user'      => $d->user->name ?? '—',
                    'amount'    => $d->amount,
                    'breakdown' => [],
                ];
            }))
            ->sortBy('date')
            ->values();

        // 👉 NO running balance here. Let Blade do it from $beginningBalance.

        return [
            'entries'          => $entries,
            'components'       => $components,
            'start'            => $startDate,
            'end'              => $endDate,
            'beginningBalance' => $beginningBalance,
        ];
    }

    private function computeTotal($transaction): float
    {
        return $transaction->details->sum(function ($d) {
            if ($d->currency === 'USD' && $d->exchange_rate) {
                return $d->total * $d->exchange_rate;
            }
            return $d->total;
        });
    }

    private function breakdownByComponent($transaction, Collection $components): array
    {
        $breakdown = [];
        foreach ($components as $c) {
            $breakdown[$c->id] = 0;
        }

        foreach ($transaction->details as $d) {
            $component = $d->feeComponent;
            if (!$component) {
                continue;
            }
            if (!isset($breakdown[$component->id])) {
                continue;
            }

            $php = ($d->currency === 'USD' && $d->exchange_rate)
                ? $d->total * $d->exchange_rate
                : $d->total;

            $breakdown[$component->id] += $php;
        }

        return $breakdown;
    }
}
