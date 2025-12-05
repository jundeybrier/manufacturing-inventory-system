<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        $requestedUserId = $request->filled('user')
            ? $request->input('user')
            : auth()->id();

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
            if ($currentUser->id !== (int) $requestedUserId) {
                abort(403, 'Unauthorized: User cannot view others.');
            }
        }

        $report = app(DailyReportService::class)->generate($date, $requestedUserId);

        return Pdf::loadView('transactions.report-pdf', $report)
            ->stream("daily-report-{$date->format('Ymd')}.pdf");
    }

    public function crr(Request $request, CrrReportService $service)
    {
        $currentUser = auth()->user();

        // -----------------------------
        // Validate input
        // -----------------------------
        $start     = $request->input('start');
        $end       = $request->input('end');
        $accountId = $request->input('account');
        $format    = $request->input('format', 'pdf'); // pdf | excel

        if (!$start || !$end || !$accountId) {
            abort(400, 'Missing required parameters: start, end, account.');
        }

        $startDate = Carbon::parse($start);
        $endDate   = Carbon::parse($end);

        // Target user
        $requestedUserId = (int) $request->input('user', $currentUser->id);
        $requestedUser   = User::findOrFail($requestedUserId);

        // -----------------------------
        // Authorization
        // -----------------------------
        if ($currentUser->can('view office reports')) {
            if ($currentUser->office_id !== $requestedUser->office_id) {
                abort(403, 'Unauthorized: Office mismatch.');
            }
        } else {
            if ($currentUser->id !== $requestedUserId) {
                abort(403, 'Unauthorized: User cannot view others.');
            }
        }

        // -----------------------------
        // Generate CRR data
        // -----------------------------
        $data = $service->generate(
            officeId:  $requestedUser->office_id,
            userId:    $requestedUser->id,
            accountId: $accountId,
            start:     $start,
            end:       $end,
        );

        // Extract cashier list (max 5)
        $uniqueCashiers = collect($data['entries'])
            ->where('type', 'receipt')
            ->pluck('user')
            ->unique()
            ->sort()
            ->take(5)
            ->values()
            ->toArray();

        // -----------------------------
        // Data for view (PDF/Excel)
        // -----------------------------
        $viewData = [
            'entries'          => $data['entries'],
            'components'       => $data['components'],
            'beginningBalance' => $data['beginningBalance'],
            'start'            => $data['start'],
            'end'              => $data['end'],
            'account'          => \App\Models\Account::find($accountId),
            'office'           => $requestedUser->office,
            'uniqueCashiers'   => $uniqueCashiers,
        ];

        // -----------------------------
        // EXCEL EXPORT
        // -----------------------------
        if ($format === 'excel') {
            return Excel::download(
                new \App\Exports\CrrExport($viewData),
                'CRR-Report.xlsx'
            );
        }

        // -----------------------------
        // PDF EXPORT
        // -----------------------------
        return Pdf::loadView('transactions.crr', $viewData)
            ->setPaper('legal', 'landscape')
            ->stream('CRR-Report.pdf');
    }


}
