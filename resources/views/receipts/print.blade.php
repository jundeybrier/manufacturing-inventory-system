<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $transaction->or_number }}</title>

    @php
        $layout = auth()->user()->receiptLayout()->first()?->toArray()
            ?? config('defaults'); // fallback if no layout set

        // extract page size
        $pageWidth  = $layout['page_width']  ?? '90mm';
        $pageHeight = $layout['page_height'] ?? '188mm';
    @endphp

    <style>
        @page {
            size: {{ $pageWidth }} {{ $pageHeight }};
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Courier New", monospace;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            position: relative;
            width: {{ $pageWidth }};
            height: {{ $pageHeight }};
        }

        .txt {
            position: absolute;
            color: #000;
            white-space: nowrap;
        }
    </style>
</head>

<body>

{{-- --------------------------------------------- --}}
{{-- OFFICE NAME --}}
{{-- --------------------------------------------- --}}
<div class="txt"
     style="
        top: {{ $layout['office_name']['y'] }}mm;
        left: {{ $layout['office_name']['x'] }}mm;
        font-size: {{ $layout['office_name']['font'] }}px;
     ">
    OFFICE OF CONSULAR AFFAIRS
</div>

{{-- --------------------------------------------- --}}
{{-- DATE --}}
{{-- --------------------------------------------- --}}
<div class="txt"
     style="
        top: {{ $layout['date']['y'] }}mm;
        left: {{ $layout['date']['x'] }}mm;
        font-size: {{ $layout['date']['font'] }}px;
     ">
    {{ now()->format('F d, Y H:i') }}
</div>

{{-- --------------------------------------------- --}}
{{-- PAYOR INFO --}}
{{-- --------------------------------------------- --}}
<div class="txt"
     style="
        top: {{ $layout['payor_info']['y'] }}mm;
        left: {{ $layout['payor_info']['x'] }}mm;
        font-size: {{ $layout['payor_info']['font'] }}px;
     ">
    Name: {{ $transaction->fullname ?? '-' }}<br>
    OR #: {{ $transaction->or_number }}<br>
    {{ ($transaction->reference ?? '') . ($transaction->remarks ? (' / ' . $transaction->remarks) : '') }}

    @if($isRevalidate ?? false)
        <div><strong>(REVALIDATED)</strong></div>
    @endif
</div>

{{-- ============================================= --}}
{{-- PARTICULARS LIST (NEW ALIGNMENT SYSTEM) --}}
{{-- ============================================= --}}

@php
    $yStart   = $layout['particulars']['y'];
    $xDesc    = $layout['particulars']['x'];
    $xAmount  = $layout['particulars']['x_amount'] ?? ($layout['total']['x'] ?? 60);
    $spacing  = $layout['particulars']['spacing'];
    $font     = $layout['particulars']['font'];

    // Compute totals per service (your original logic)
    $items = [];
    foreach ($transaction->details->groupBy('service_id') as $serviceId => $group) {
        $serviceName = $group->first()->service->name ?? "Unknown";

        $totalPhp = $group->sum(function($i) {
            if ($i->currency === 'USD' && $i->exchange_rate) {
                return $i->total * $i->exchange_rate;
            }
            return $i->total;
        });

        $items[] = [
            'desc' => Str::limit($serviceName, 32),
            'amt'  => "₱" . number_format($totalPhp, 2),
        ];
    }
@endphp

{{-- DESCRIPTIONS --}}
@foreach ($items as $i => $row)
    <div class="txt"
         style="
            top: {{ $yStart + ($i * $spacing) }}mm;
            left: {{ $xDesc }}mm;
            font-size: {{ $font }}px;
         ">
        {{ $row['desc'] }}
    </div>
@endforeach

{{-- AMOUNTS (Right-aligned using transform: translateX(-100%)) --}}
@foreach ($items as $i => $row)
    <div class="txt"
         style="
            top: {{ $yStart + ($i * $spacing) }}mm;
            left: {{ $xAmount }}mm;
            transform: translateX(-100%);
            text-align: right;
            font-size: {{ $font }}px;
            white-space: nowrap;
         ">
        {{ $row['amt'] }}
    </div>
@endforeach

@php
    $totalPhp = $transaction->details->sum(function ($i) {
        return ($i->currency === 'USD' && $i->exchange_rate)
            ? $i->total * $i->exchange_rate
            : $i->total;
    });
@endphp

{{-- --------------------------------------------- --}}
{{-- TOTAL --}}
{{-- --------------------------------------------- --}}
<div class="txt"
     style="
        top: {{ $layout['total']['y'] }}mm;
        left: {{ $layout['total']['x'] }}mm;
        transform: translateX(-100%);
        text-align: right;
        font-size: {{ $layout['total']['font'] }}px;
     ">
    ₱{{ number_format($totalPhp, 2) }}
</div>

{{-- --------------------------------------------- --}}
{{-- AMOUNT IN WORDS --}}
{{-- --------------------------------------------- --}}
<div class="txt"
     style="
        top: {{ $layout['amount_words']['y'] }}mm;
        left: {{ $layout['amount_words']['x'] }}mm;
        font-size: {{ $layout['amount_words']['font'] }}px;
     ">
    {{ App\Helpers\NumberToWords::convert($totalPhp ?? 0) }} pesos only.
</div>

{{-- --------------------------------------------- --}}
{{-- CASHIER NAME --}}
{{-- --------------------------------------------- --}}
<div class="txt"
     style="
        top: {{ $layout['cashier_name']['y'] }}mm;
        left: {{ $layout['cashier_name']['x'] }}mm;
        font-size: {{ $layout['cashier_name']['font'] }}px;
        text-align: center;
        width: 100%;
     ">
    {{ auth()->user()->name }}
</div>


<script>
    window.onload = () => {
        window.print();
        setTimeout(() => window.close(), 500);
    };
</script>

</body>
</html>
