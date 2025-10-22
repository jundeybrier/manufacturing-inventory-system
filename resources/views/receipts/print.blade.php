<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $transaction->or_number }}</title>
    <style>
        @page {
            size: 90mm 188mm;
            margin: 5mm 5mm 5mm 8mm;
        }

        body {
            font-family: Consolas, "Lucida Console", "Courier New", monospace;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
            width: 90mm;
            text-align: left;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        hr { border: none; border-top: 1px dashed #666; margin: 4px 0; }

        .center { text-align: center; }
        .right  { text-align: right; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }

        th, td {
            padding: 2px 0;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            color: #000;
        }

        .pad-right-10 { padding-right: 22px; }
        td.desc { width: 72%; }
        td.amount { width: 28%; text-align: right; }

        @media print {
            body {
                font-family: "Roboto Mono", monospace, "Lucida Console", Consolas, "Courier New", monospace;
                font-size: 12px !important;
                color: #000;
            }
        }
    </style>
</head>
<body>
<div class="center" style="margin-top:90px;">
    OFFICE OF CONSULAR AFFAIRS
</div>

<div style="margin-top:65px;">
    {{ now()->format('F d, Y H:i') }}<br>
</div>

<div style="margin-top:30px;">
    Name: {{ $transaction->fullname ?? '-' }}<br>
    OR #: {{ $transaction->or_number }}<br>
    {{ ($transaction->reference ?? '') . ($transaction->remarks ? (' / ' . $transaction->remarks) : '') }}
    @if($isRevalidate ?? false)
        <div><strong>(REVALIDATED)</strong></div>
    @endif
</div>

@php
    $totalLines = 10;

    // ✅ Compute total per service, with USD converted to PHP using its own exchange_rate
    $grouped = $transaction->details
        ->groupBy('service_id')
        ->map(function($items) {
            $service = $items->first()->service->name ?? 'Unknown Service';

            $totalPhp = $items->sum(function($i) {
                if ($i->currency === 'USD' && $i->exchange_rate) {
                    return $i->total * $i->exchange_rate; // convert to PHP
                }
                return $i->total;
            });

            $hasUsd = $items->contains(fn($i) => $i->currency === 'USD');

            return [
                'service_name' => $service,
                'has_usd'      => $hasUsd,
                'usd_details'  => $hasUsd
                    ? $items->filter(fn($i) => $i->currency === 'USD')
                        ->map(fn($i) => [
                            'usd' => $i->total,
                            'rate' => $i->exchange_rate,
                            'php' => $i->total * $i->exchange_rate
                        ])
                    : collect(),
                'total_php' => $totalPhp,
            ];
        });
@endphp

<table style="margin-top:53px;">
    <tbody>
    @foreach($grouped as $g)
        <tr>
            <td class="desc">{{ Str::limit($g['service_name'], 32) }}</td>
            <td class="amount right pad-right-10">
                ₱{{ number_format($g['total_php'], 2) }}
            </td>
        </tr>

        {{-- If there are USD components, show breakdown --}}
        @if($g['has_usd'])
            @foreach($g['usd_details'] as $u)
                <tr>
                    <td class="desc" style="padding-left: 10px;">
                        (USD {{ number_format($u['usd'], 2) }} @ ₱{{ number_format($u['rate'], 2) }})
                    </td>
                    <td class="amount right pad-right-10">

                    </td>
                </tr>
            @endforeach
        @endif

        @php $totalLines--; @endphp
    @endforeach

    {{-- Fill empty rows for alignment --}}
    @while($totalLines > 0)
        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        @php $totalLines--; @endphp
    @endwhile
    </tbody>

    <tfoot>
    <tr>
        <td class="right"></td>
        <td class="amount pad-right-10">
            ₱{{ number_format($transaction->details->sum(fn($i) =>
                $i->currency === 'USD' && $i->exchange_rate
                    ? $i->total * $i->exchange_rate
                    : $i->total
            ), 2) }}
        </td>
    </tr>
    </tfoot>
</table>

<table style="margin-top:150px;">
    <tr>
        <td width="50%">&nbsp;</td>
        <td width="50%" style="text-align:center;">{{ auth()->user()->name ?? '' }}</td>
    </tr>
</table>

<script>
    window.onload = () => {
        window.print();
        setTimeout(() => window.close(), 500);
    };
</script>
</body>
</html>
