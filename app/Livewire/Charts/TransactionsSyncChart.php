<?php

namespace App\Livewire\Charts;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransactionsSyncChart extends Component
{
    public $labels = [];
    public $totals = [];
    public $synced = [];

    public function mount()
    {
        $officeId = auth()->user()->office_id;
        $fromDate = now()->subDays(29)->startOfDay();

        $data = DB::table('transactions')
            ->selectRaw('DATE(created_at) as date,
                         COUNT(*) as total_transactions,
                         SUM(CASE WHEN synced_at IS NOT NULL THEN 1 ELSE 0 END) as synced_transactions')
            ->where('office_id', $officeId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $fromDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        for ($date = $fromDate->clone(); $date <= now(); $date->addDay()) {
            $formatted = $date->toDateString();
            $row = $data->firstWhere('date', $formatted);

            $this->labels[] = $date->format('M d');
            $this->totals[] = $row->total_transactions ?? 0;
            $this->synced[] = $row->synced_transactions ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.charts.transactions-sync-chart');
    }
}
