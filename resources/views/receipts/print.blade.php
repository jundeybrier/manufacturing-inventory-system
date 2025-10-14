<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $transaction->or_number }}</title>
    <style>
        @font-face {
            font-family: 'DotMatrix';
            src: url('{{ asset('fonts/DotMatrix.woff2') }}') format('woff2'),
            url('{{ asset('fonts/DotMatrix.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            size: 90mm 188mm;
            margin: 5mm 5mm 5mm 8mm;
        }

        body {
            /* ✅ slightly thicker, easier-to-read text */
            font-family: 'DotMatrix', 'Courier New', monospace;
            font-size: 12px;
            font-weight: 500;   /* not bold, just medium weight */
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

        hr {
            border: none;
            border-top: 1px dashed #666;
            margin: 4px 0;
        }

        .center { text-align: center; }
        .right  { text-align: right; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        th, td {
            padding: 2px 0;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            color: #000;
        }

        th {
            text-align: left;
            border-bottom: 1px dashed #666;
        }

        .pad-right-10{
            padding-right: 22px;
        }

        td.desc { width: 75%; }
        td.amount { width: 25%; text-align: right; }

        @media print {
            body {
                font-family: 'DotMatrix', 'Courier New', monospace !important;
                font-size: 12px !important;
                font-weight: 1500 !important;
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

<div style="margin-top:28px;">
    Name: {{ $transaction->customer_name ?? '-' }}<br>
    OR #: {{ $transaction->or_number }}
    @if($isRevalidate ?? false)
        <div><strong>(REVALIDATED)</strong></div>
    @endif
</div>
<?php
    $totalLines = 9;
?>
<table style="margin-top:65px;">
    <tbody>
    @foreach($transaction->details as $d)
        <tr>
            <td class="desc">{{ Str::limit($d->description ?? $d->name, 35) }}</td>
            <td class="amount right pad-right-10">₱{{ number_format($d->amount, 2) }}</td>
        </tr>
        <?php $totalLines--; ?>
    @endforeach
    <?php
        while($totalLines>=1){
            $totalLines--;
            echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
        }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td class="right"></td>
        <td class="amount pad-right-10"><strong>₱{{ number_format($transaction->total_amount, 2) }}</strong></td>
    </tr>
    </tfoot>
</table>

<br>
Cashier: {{ auth()->user()->name ?? '' }}

<script>
    window.onload = () => {
        window.print();
        setTimeout(() => window.close(), 500);
    };
</script>
</body>
</html>
