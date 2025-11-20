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
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : today();

        $requestedUserId = $request->input('user', auth()->id());

        $requestedUser = \App\Models\User::findOrFail($requestedUserId);
        $currentUser = auth()->user();

        // Supervisors can only view reports of users within same office
        if ($currentUser->can('view office reports')) {
            if ($currentUser->office_id !== $requestedUser->office_id) {
                abort(403, 'Unauthorized: Office mismatch.');
            }
        }
        // Tellers can only view themselves
        else {
            if ($currentUser->id !== $requestedUserId) {
                abort(403, 'Unauthorized: Teller cannot view others.');
            }
        }

        $report = app(DailyReportService::class)->generate($date, $requestedUserId);

        return Pdf::loadView('transactions.report-pdf', $report)
            ->stream("daily-report-{$date->format('Ymd')}.pdf");
    }

}
