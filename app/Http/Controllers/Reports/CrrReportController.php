<?php

namespace App\Http\Controllers\Reports;

use App\Exports\CrrExport;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use App\Services\Reports\CrrReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CrrReportController extends Controller
{
    public function index(Request $request, CrrReportService $service)
    {
        $currentUser = auth()->user();

        // --------- INPUTS ----------
        $start      = $request->input('start');
        $end        = $request->input('end');
        $accountId  = $request->input('account');
        $format     = $request->input('format', 'pdf');   // pdf | excel
        $userId     = (int) $request->input('user', $currentUser->id);

        // --------- VALIDATION ----------
        if (!$start || !$end || !$accountId) {
            abort(400, "Missing required parameters: start, end, account.");
        }

        // --------- AUTHORIZATION ----------
        $requestedUser = User::findOrFail($userId);

        if ($currentUser->can('view office reports')) {
            if ($currentUser->office_id !== $requestedUser->office_id) {
                abort(403, 'Unauthorized: Office mismatch.');
            }
        }
        else {
            if ($currentUser->id !== $requestedUser->id) {
                abort(403, 'Unauthorized: Cannot view others.');
            }
        }

        // --------- GENERATE CRR DATA ----------
        $data = $service->generate(
            officeId:   $requestedUser->office_id,
            userId:     $requestedUser->id,
            accountId:  $accountId,
            start:      $start,
            end:        $end,
        );

        // --------- Extract Cashiers (max 5) ----------
        $uniqueCashiers = collect($data['entries'])
            ->where('type', 'receipt')
            ->pluck('user')
            ->unique()
            ->sort()
            ->take(5)
            ->values()
            ->toArray();

        // --------- COMMON VIEW DATA ----------
        $viewData = [
            'entries'          => $data['entries'],
            'components'       => $data['components'],
            'beginningBalance' => $data['beginningBalance'],
            'start'            => $data['start'],
            'end'              => $data['end'],
            'account'          => Account::find($accountId),
            'office'           => $requestedUser->office,
            'uniqueCashiers'   => $uniqueCashiers,
        ];

        // --------- PDF EXPORT ----------
        if ($format === 'pdf') {
            return Pdf::loadView('transactions.crr', $viewData)
                ->setPaper('folio', 'landscape')
                ->stream('CRR-Report.pdf');
        }

        // --------- EXCEL EXPORT ----------
        if ($format === 'excel') {
            return Excel::download(
                new CrrExport($viewData),
                'CRR-Report.xlsx'
            );
        }

        // --------- INVALID FORMAT ----------
        abort(400, 'Invalid format. Must be pdf or excel.');
    }
}
