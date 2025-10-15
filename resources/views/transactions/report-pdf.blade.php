<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Collections Report</title>
    <style>
        body { font-family: Arial, sans-serif;font-size: 0.8em; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bordered, .border-left, .border-right { border: 1px solid #222; }
        .double-bottom { border-bottom: 3px double #222; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; font-size: 10px; }
    </style>
</head>
<body>

<table>
    <tr>
        <td class="center" style="font-size:0.8em;">
            Republic of the Philippines<br>
            Department of Foreign Affairs<br>
            Office of Consular Affairs<br>
            <u><b style="font-size:1.2em;">{{ strtoupper($user->office->site ?? '') }}</b></u><br>
            DAILY COLLECTIONS REPORT<br>
            {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
        </td>
    </tr>
</table>

<br>

@php
    $types = ['passport', 'authentication', 'notarials', 'others'];
    $grandTotal = 0;
@endphp

<table border="1">
    <thead>
    <tr>
        <th>CONSULAR SERVICE INCOME</th>
        <th>WORK UNITS</th>
        <th>UNIT PRICE</th>
        <th>AMOUNT</th>
        <th>BSP CONVERSION RATE USED</th>
        <th>TOTAL AMOUNT IN PHP</th>
        <th>TOTAL PER SERVICE INCOME</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($grouped as $type => $components)
        @php $typeTotal = collect($components)->sum('converted'); @endphp
        <tr>
            <td><u><b>{{ $type }}</b></u></td>
            <td colspan="6"></td>
        </tr>

        @foreach ($components as $row)
            <tr>
                <td>{{ $row['component'] }}</td>
                <td class="center">{{ $row['work_units'] }}</td>
                <td class="center">{{ number_format($row['unit_price'], 2) }}</td>
                <td class="center">{{ number_format($row['amount'], 2) }}</td>
                <td class="center">{{ $row['rate'] ? number_format($row['rate'], 2) : '-' }}</td>
                <td class="right">{{ number_format($row['converted'], 2) }}</td>

                @if ($loop->last)
                    <td class="right">{{ number_format($typeTotal, 2) }}</td>
                @else
                    <td></td>
                @endif
            </tr>
        @endforeach

        @php $grandTotal += $typeTotal; @endphp
    @endforeach

    <tr>
        <td class=""><b>TOTAL</b></td>
        <td colspan="4"></td>
        <td class="right">{{ number_format($grandTotal, 2) }}</td>
        <td class="double-bottom right"><b>{{ number_format($grandTotal, 2) }}</b></td>
    </tr>
    </tbody>
</table>




<br>
{{-- ISSUED OR RANGE --}}
<table>
    <tr>
        <td colspan="2"><b>OFFICIAL RECEIPTS ISSUED</b></td>
        <td class="center"><b>FROM</b></td>
        <td class="center"><b>TO</b></td>
        <td class="center" colspan="2"><b># of Official Receipt(s) Issued</b></td>
        <td colspan="1"></td>
    </tr>
    @php
        $orNumbers = $transactions->pluck('or_number')->sort()->values();
        $orStart = $orNumbers->first();
        $orEnd = $orNumbers->last();
    @endphp
    <tr>
        <td colspan="2"></td>
        <td class="center">{{ $orStart }}</td>
        <td class="center">{{ $orEnd }}</td>
        <td class="center" colspan="2">{{ ($orEnd - $orStart) + 1 }}</td>
        <td colspan="1"></td>
    </tr>
</table>

<br>

@php
    $allAccounts = collect($accountSummaryTable)->collapse()->keys()->unique();
    $columnTotals = [];
    $grandTotal = 0;
@endphp

<table class="" border="1">
    <thead>
    <tr>
        <th rowspan="2">PARTICULARS</th>
        <th colspan="{{ count($allAccounts)+1  }}">CASH DISTRIBUTION FOR DEPOSIT SLIP PREPARATION</th>
    </tr>
    <tr>
        @foreach ($allAccounts as $account)
            <th>{{ $account }}</th>
        @endforeach
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($accountSummaryTable as $type => $accounts)
        <tr>
            <td>{{ $type }}</td>
            @php $rowTotal = 0; @endphp
            @foreach ($allAccounts as $account)
                @php
                    $val = $accounts[$account] ?? 0;
                    $rowTotal += $val;
                    $columnTotals[$account] = ($columnTotals[$account] ?? 0) + $val;
                    $grandTotal += $val;
                @endphp
                @if($val>0)
                    <td class="text-end right">{{ number_format($val, 2) }}</td>
                @else
                    <td></td>
                @endif
            @endforeach
            <td class="text-end fw-bold right">{{ number_format($rowTotal, 2) }}</td>
        </tr>
    @endforeach

    {{-- Totals Row --}}
    <tr class="fw-bold">
        <th>Total</th>
        @foreach ($allAccounts as $account)
            <th class="text-end right">{{ number_format($columnTotals[$account], 2) }}</th>
        @endforeach
        <th class="text-end right">{{ number_format($grandTotal, 2) }}</th>
    </tr>
    </tbody>
</table>

<br>
<table width="100%">
    <tr>
        <td width="55%" style="vertical-align:top;">
            <table style="border: 1px solid #f4f4f4;font-size:0.8em;padding:10px;width:100%;">
                <tr>
                    <td class="center" style="padding:5px;" width="60%">Certification</td>
                    <td class="center" style="padding:5px;" width="40%">Name/Designation/Signature</td>
                </tr>
            </table>
            <table style="border: 1px solid #f4f4f4;font-size:0.8em;padding:10px;margin-top:4px;" width="100%">
                <tr>
                    <td class="center" style="padding:5px;" width="60%">Daily collection were duly receipted and accounted for and are under my accountability</td>
                    <td class="center" style="padding:5px;" width="40%"><br><u><?= strtoupper($user->fullname) ?></u><br>Collecting Officer</td>
                </tr>
            </table>
            <table style="border: 1px solid #f4f4f4;font-size:0.8em;padding:10px;margin-top:4px;" width="100%">
                <tr>
                    <td class="center" style="padding:5px;" width="60%">No. of Work Units tallies with Consular records</td>
                    <td class="center" style="padding:5px;" width="40%"><br><u><?= strtoupper($user->office->consular_officer) ?></u><br><?= $user->office->consular_officer_designation ?></td>
                </tr>
            </table>
            <table style="border: 1px solid #f4f4f4;font-size:0.8em;padding:10px;margin-top:4px;" width="100%">
                <tr>
                    <td class="center" style="padding:5px;" width="60%">Total Collection and Deposits Slips were verified and found correct</td>
                    <td class="center" style="padding:5px;" width="40%"><br><u><?= strtoupper($user->office->administrative_officer) ?></u><br><?= $user->office->administrative_officer_designation ?></td>
                </tr>
            </table>
            <table style="border: 1px solid #f4f4f4;font-size:0.8em;padding:10px;margin-top:4px;" width="100%">
                <tr>
                    <td class="center" style="padding:5px;vertical-align:top;" width="60%">Noted by:</td>
                    <td class="center" style="padding:5px;" width="40%"><br><br><u><?= strtoupper($user->office->head_of_consular_office) ?></u><br><?= $user->office->head_of_consular_office_designation ?></td>
                </tr>
            </table>
        </td>
        <td width="2%">&nbsp;</td>
        <td width="43%" style="vertical-align:top;">
            <table border="1">
                <tr>
                    <th>DENOMINATION</th>
                    <th>QUANTITY</th>
                    <th>TOTAL</th>
                </tr>
                @php $cb_total = 0; @endphp
                @foreach ($denominations as $denomination)
                    @php
                        // You should compute quantity based on your own logic/data
                        $quantity = 0; // Dummy: replace with real value
                        $total = $quantity * $denomination;
                        $cb_total += $total;
                    @endphp
                    <tr>
                        <td class="center">{{ number_format($denomination,2) }}</td>
                        <td class="center">{{ $quantity }}</td>
                        <td class="right">{{ $total > 0 ? number_format($total,2) : '-' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2"><b>TOTAL</b></td>
                    <td class="right"><b>{{ number_format($cb_total,2) }}</b></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{{-- DENOMINATION TABLE --}}


<br style="page-break-after: always;">

<div style="page-break-after: always;"></div>
{{-- SUMMARY OF DAILY TRANSACTIONS --}}
<table width="100%">
    <tr>
        <td>
            <b>Department of Foreign Affairs</b><br>
            {{ strtoupper($user->office->site ?? '') }}<br>
            Summary of Daily Transactions<br>
            {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
        </td>
    </tr>
</table>
<br>
<table border="1">
    <thead>
    <tr>
        <th style="width: 3%;">#</th>
        <th style="width: 5%;">Ref #</th>
        <th style="width: 5%;">OR #</th>
        <th style="width: 30%;">Client's Fullname</th>
        <th style="width: 5%;"># of Txns</th>
        @foreach ($allAccounts as $account)
            <th>{{ $account }}</th>
        @endforeach
        <th style="width: 12%;">Total</th>
    </tr>
    </thead>
    <tbody>
    @php
        $grandTotal = 0;
        $accountTotals = array_fill_keys($allAccounts->toArray(), 0);
        $txnCountTotal = 0;
    @endphp

    @foreach ($transactions as $i => $txn)
        @php
            $rowTotal = 0;
            $txnCountTotal += $txn->details->first()->quantity;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $txn->ref_no }}</td>
            <td>{{ $txn->or_number }}</td>
            <td>{{ $txn->fullname }}</td>
            <td class="center">{{ $txn->details->first()->quantity }}</td>

            @foreach ($allAccounts as $acc)
                @php
                    $row = $txn->getTotalAccount($acc);
                    $rowTotal += $row;
                    $accountTotals[$acc] += $row;
                @endphp
                <td class="right">
                    {{ number_format($row, 2) }}
                </td>
            @endforeach

            @php $grandTotal += $rowTotal; @endphp
            <td class="right font-bold">
                {{ number_format($rowTotal, 2) }}
            </td>
        </tr>
    @endforeach

    {{-- TOTAL ROW --}}
    <tr>
        <td colspan="4" class="right font-bold">TOTAL</td>
        <td class="center font-bold">{{ $txnCountTotal }}</td>
        @foreach ($allAccounts as $acc)
            <td class="right font-bold">{{ number_format($accountTotals[$acc], 2) }}</td>
        @endforeach
        <td class="right font-bold">{{ number_format($grandTotal, 2) }}</td>
    </tr>
    </tbody>
</table>



<div style="page-break-after: always;"></div>
{{-- SUMMARY OF DAILY TRANSACTIONS --}}
<table width="100%">
    <tr>
        <td>
            <b>Department of Foreign Affairs</b><br>
            {{ strtoupper($user->office->site ?? '') }}<br>
            Detailed Daily Transactions<br>
            {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
        </td>
    </tr>
</table>
<br>
<table border="1">
    <thead>
    <tr>
        <th style="width: 3%;">#</th>
        <th style="width: 5%;">Ref #</th>
        <th style="width: 5%;">OR #</th>
        <th style="width: 30%;">Services Availed</th>
        <th style="width: 5%;">Quantity</th>
        @foreach ($allAccounts as $account)
            <th>{{ $account }}</th>
        @endforeach
        <th style="width: 12%;">Total</th>
    </tr>
    </thead>
    <tbody>
    @php
        $grandTotal = 0;
        $accountTotals = array_fill_keys($allAccounts->toArray(), 0);
        $txnCountTotal = 0;
    @endphp

    @foreach ($transactions as $i => $txn)

        @foreach ($txn->details as $txnDetails)
            @php
                $rowTotal = 0;
                $txnCountTotal += $txn->details->first()->quantity;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $txn->ref_no }}</td>
                <td>{{ $txn->or_number }}</td>
                <td>{{ $txnDetails->feeComponent->name }}</td>
                <td class="center">{{ $txn->details->first()->quantity }}</td>

                @foreach ($allAccounts as $acc)
                    @php
                        $row = $txnDetails->getTotalAccount($acc);
                        $rowTotal += $row;
                        $accountTotals[$acc] += $row;
                    @endphp
                    <td class="right">
                        {{ number_format($row, 2) }}
                    </td>
                @endforeach

                @php $grandTotal += $rowTotal; @endphp
                <td class="right font-bold">
                    {{ number_format($rowTotal, 2) }}
                </td>
            </tr>
        @endforeach

    @endforeach

    {{-- TOTAL ROW --}}
    <tr>
        <td colspan="4" class="right font-bold">TOTAL</td>
        <td class="center font-bold">{{ $txnCountTotal }}</td>
        @foreach ($allAccounts as $acc)
            <td class="right font-bold">{{ number_format($accountTotals[$acc], 2) }}</td>
        @endforeach
        <td class="right font-bold">{{ number_format($grandTotal, 2) }}</td>
    </tr>
    </tbody>
</table>
@php
    $voidedTransactions = \App\Models\Transaction::with(['details', 'voidedBy'])
        ->whereDate('voided_at', $date)
        ->where('is_voided', true)
        ->orderBy('voided_at', 'asc')
        ->get();
@endphp
@if($voidedTransactions->count())
{{-- PAGE: VOIDED TRANSACTIONS SUMMARY --}}
<div style="page-break-after: always;"></div>

<table width="100%">
    <tr>
        <td class="center">
            <b>Department of Foreign Affairs</b><br>
            {{ strtoupper($user->office->site ?? '') }}<br>
            <b>Voided Transactions Summary</b><br>
            {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
        </td>
    </tr>
</table>

<br>




    <table border="1" width="100%">
        <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 10%;">OR No.</th>
            <th style="width: 20%;">Client Name</th>
            <th style="width: 30%;">Services Availed</th>
            <th style="width: 20%;">Reason / Explanation</th>
            <th style="width: 15%;">Voided By / Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach($voidedTransactions as $index => $txn)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ $txn->or_number }}</td>
                <td>{{ trim(($txn->firstname ?? '').' '.($txn->middlename ?? '').' '.($txn->lastname ?? '')) ?: '-' }}</td>
                <td>
                    @foreach($txn->details as $detail)
                        <li>{{ $detail->feeComponent->name }} – Php {{ number_format($detail->amount, 2) }}</li>
                    @endforeach
                </td>
                <td>{{ $txn->void_reason }}</td>
                <td class="center">
                    {{ strtoupper(optional($txn->voidedBy)->fullname ?? 'N/A') }}<br>
                    {{ \Carbon\Carbon::parse($txn->voided_at)->format('d M Y h:i A') }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endif

@if($voidedTransactions->count())
    @foreach($voidedTransactions as $txn)
        <div style="page-break-before: always;">

            <table width="100%">
                <tr>
                    <td class="center">
                        <b>Republic of the Philippines</b><br>
                        <b>Department of Foreign Affairs</b><br>
                        <b>Office of Consular Affairs</b><br>
                        <b>{{ strtoupper($user->office->site ?? '') }}</b><br><br>
                        <b style="font-size:1.2em;">CERTIFICATION OF VOIDED TRANSACTION</b>
                    </td>
                </tr>
            </table>

            <br>

            <p style="text-align:justify; line-height:1.6;">
                This is to certify that the following transaction has been voided from today’s collection record.
            </p>

            <table border="1" width="100%" style="border-collapse: collapse; font-size: 10px; margin-top: 10px;">
                <tr>
                    <th style="width: 25%;">Official Receipt (OR) No.</th>
                    <td style="width: 25%;">{{ $txn->or_number }}</td>
                    <th style="width: 25%;">Date of Transaction</th>
                    <td style="width: 25%;">
                        {{ \Carbon\Carbon::parse($txn->date ?? $txn->created_at)->format('F d, Y') }}
                    </td>
                </tr>
                <tr>
                    <th>Client Name</th>
                    <td colspan="3">
                        {{ trim(($txn->firstname ?? '').' '.($txn->middlename ?? '').' '.($txn->lastname ?? '')) ?: 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <th>Services Availed</th>
                    <td colspan="3">
                        <ul style="margin:0; padding-left:15px;">
                            @foreach($txn->details as $detail)
                                <li>{{ $detail->feeComponent->name }} – Php {{ number_format($detail->amount, 2) }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td colspan="3">Php {{ number_format($txn->total_amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <th>Reason / Explanation</th>
                    <td colspan="3">{{ $txn->void_reason }}</td>
                </tr>
                <tr>
                    <th>Voided By</th>
                    <td>
                        {{ strtoupper(optional($txn->voidedBy)->fullname ?? $user->fullname) }}
                    </td>
                    <th>Date / Time Voided</th>
                    <td>
                        {{ \Carbon\Carbon::parse($txn->voided_at)->format('F d, Y h:i A') }}
                    </td>
                </tr>
            </table>

            <br>
            <p style="text-align:justify; line-height:1.6;">
                The above transaction has been verified and approved for voiding in accordance with office procedures.
                The original official receipt has been properly marked <b>“VOID”</b> and retained for reference and audit purposes.
            </p>

            <br><br>

            <table width="100%">
                <tr>
                    <td class="center" style="width:50%;font-size:12px;">
                        <u>{{ strtoupper(optional($txn->voidedBy)->fullname ?? $user->fullname) }}</u><br>
                        Voided By<br>
                        {{ \Carbon\Carbon::parse($txn->voided_at)->format('F d, Y h:i A') }}
                    </td>
                    <td class="center" style="width:50%;font-size:12px;">
                        <u>{{ strtoupper($user->office->head_of_consular_office ?? 'N/A') }}</u><br>
                        Head of Consular Office
                    </td>
                </tr>
            </table>

            <br><br>
            <p class="center" style="font-size:0.85em;">
                (This certification page is automatically generated as part of the Daily Collections Report)
            </p>

        </div>
    @endforeach

@endif


</body>
</html>
