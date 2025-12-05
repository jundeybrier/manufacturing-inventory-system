<!DOCTYPE html>
<html>
<head>
    <title>CRR Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse !important;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        .no-border { border: 0 !important; }
        .header-center { text-align: center; font-weight: bold; }
        .logo { width: 70px; height: 70px;}
        .income-col {
            width: 80px;
            max-width: 80px;
            white-space: normal;
            text-align: center;
            font-size: 9px;
        }
    </style>
</head>

<body>

{{-- ===================== HEADER ===================== --}}
<table class="no-border">
    <tr>
        <td class="no-border" style="width: 15%; text-align:left;">
            <div class="logo"><img src="{{ public_path('images/dfa_logo.png') }}" width="70"></div>
        </td>

        <td class="no-border header-center" style="width: 70%;">
            <div>Republic of the Philippines</div>
            <div>Department of Foreign Affairs</div>
            <div>Office of Consular Affairs</div>
            <div>{{ strtoupper($office->site ?? '') }}</div>
        </td>

        <td class="no-border" style="width: 15%; text-align:right;">
            <div class="logo"><img src="{{ public_path('images/Bagong_Pilipinas_logo.png') }}" width="70"></div>
        </td>
    </tr>
</table>

{{-- ===================== SUB HEADING ===================== --}}
<div class="header-center" style="margin-top: 10px; font-size:14px;">
    <strong>CASH RECEIPT REGISTER – {{ strtoupper($account->name) }}</strong>
</div>

<div class="header-center" style="margin-bottom: 10px; font-size:12px;">
    PERIOD: {{ $start->format('F d, Y') }} – {{ $end->format('F d, Y') }}
</div>

{{-- ===================== TABLE HEADER ===================== --}}
<table>
    <thead>
    <tr>
        <th rowspan="3">DATE</th>
        <th rowspan="3">OR / REF NO.</th>
        <th rowspan="3">PAYOR / PARTICULARS</th>
        <th rowspan="3">CASHIER</th>

        {{-- Cash Collection Officer 102 --}}
        <th colspan="3">CASH COLLECTION OFFICER (102)</th>

        {{-- Dynamic income columns --}}
        @if(count($components)>0)
            <th colspan="{{ count($components) }}">CONSULAR SERVICE INCOME</th>
        @else
            <th rowspan="3">CONSULAR SERVICE INCOME</th>
        @endif

    </tr>

    <tr>
        <th>RECEIPTS</th>
        <th>DEPOSITS</th>
        <th>BALANCE</th>

        {{-- Dynamic Header Row: Service / Component --}}
        @foreach($components as $comp)
            <th rowspan="2" class="income-col">
                {{ strtoupper($comp->name) }}
            </th>
        @endforeach
    </tr>

    <tr>
        <th>(+)</th>
        <th>(-)</th>
        <th>(=)</th>
    </tr>
    </thead>

    <tbody>
    @php
        $balance = $beginningBalance;
    @endphp

    {{-- BEGINNING BALANCE ROW --}}
    <tr>
        <td>{{ $start->format('Y-m-d') }}</td>
        <td>—</td>
        <td><strong>BEGINNING BALANCE</strong></td>
        <td>—</td>

        <td></td>
        <td></td>

        <td><strong>{{ number_format($beginningBalance, 2) }}</strong></td>

        @if(count($components)>0)
            @foreach($components as $comp)
                <td></td>
            @endforeach
        @else
            <td></td>
        @endif


    </tr>

    {{-- NORMAL ENTRIES --}}
    @foreach($entries as $e)
        @php
            $receiptAmount = $e['type'] === 'receipt' ? (float) $e['amount'] : 0;
            $depositAmount = $e['type'] === 'deposit' ? (float) $e['amount'] : 0;

            $balance += $receiptAmount - $depositAmount;
        @endphp

        <tr>
            <td>{{ \Carbon\Carbon::parse($e['date'])->format('Y-m-d') }}</td>
            <td>{{ $e['or'] }}</td>
            <td>{{ $e['payor'] }}</td>
            <td>{{ $e['user'] }}</td>

            <td>
                @if($receiptAmount > 0)
                    {{ number_format($receiptAmount, 2) }}
                @endif
            </td>

            <td>
                @if($depositAmount > 0)
                    {{ number_format($depositAmount, 2) }}
                @endif
            </td>

            <td>{{ number_format($balance, 2) }}</td>

            @if(count($components)>0)
            @foreach($components as $comp)
                <td>
                    @if($e['type'] === 'receipt')
                        {{ number_format($e['breakdown'][$comp->id] ?? 0, 2) }}
                    @endif
                </td>
            @endforeach
            @else
                <td></td>
            @endif
        </tr>
    @endforeach
    </tbody>

</table>
<br><br>
@php
    $roles = auth()->user()->pref('report_roles', []);

    // list of up to 5 cashier names from controller
    $collectorList = $uniqueCashiers ?? [];

    $collector = implode(", ", array_map('strtoupper', $collectorList));

    $consular = $roles['consular_supervisor'] ?? null;
    $admin = $roles['administrative_officer'] ?? null;
    $head = $roles['head_of_consular_office'] ?? null;
@endphp
<table width="100%" style="text-align:center;">
    <tr>
        <td width="25%" style="border: 0px;">
        @foreach ($uniqueCashiers as $cashier)

                <u>{{ strtoupper($cashier) }}</u><br>
                Collecting Officer
                <br><br>
        @endforeach
        </td>

        <td width="25%" style="border: 0px;">
            <u>{{ strtoupper($consular['name'] ?? '') }}</u><br>
            {{ $consular['designation'] ?? '' }}
        </td>

        {{-- Administrative Officer --}}
        <td width="25%" style="border: 0px;">
            <u>{{ strtoupper($admin['name'] ?? '') }}</u><br>
            {{ $admin['designation'] ?? '' }}
        </td>

        {{-- Head of Consular Office --}}
        <td width="25%" style="border: 0px;">
            <u>{{ strtoupper($head['name'] ?? '') }}</u><br>
            {{ $head['designation'] ?? '' }}
        </td>
    </tr>
</table>

</body>
</html>
