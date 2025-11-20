<?php

namespace App\Http\Controllers;

use App\Services\Reports\DailyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = request('date') ? Carbon::parse(request('date')) : today();
        $userId = request('user') ?? auth()->id();

// Always enforce office scope — even for admins
        $user = \App\Models\User::findOrFail($userId);

        if (!auth()->user()->can('view office reports') && auth()->id() !== $userId) {
            abort(403, 'Unauthorized report access');
        }

        $report = app(DailyReportService::class)->generate($date, $userId);

        return Pdf::loadView('transactions.report-pdf', $report)
            ->stream("daily-report-{$date->format('Ymd')}.pdf");
    }
}
