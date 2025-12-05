<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>OR / Ref</th>
        <th>Payor</th>
        <th>Cashier</th>
        <th>Receipt (+)</th>
        <th>Deposit (-)</th>
        <th>Balance</th>
        @foreach($components as $comp)
            <th>{{ $comp->name }}</th>
        @endforeach
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>{{ $start->format('Y-m-d') }}</td>
        <td></td>
        <td>BEGINNING BALANCE</td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $beginningBalance }}</td>
        @foreach($components as $comp)
            <td></td>
        @endforeach
    </tr>

    @php $balance = $beginningBalance; @endphp

    @foreach($entries as $e)
        @php
            $receipt = $e['type'] === 'receipt' ? $e['amount'] : 0;
            $deposit = $e['type'] === 'deposit' ? $e['amount'] : 0;
            $balance += $receipt - $deposit;
        @endphp

        <tr>
            <td>{{ $e['date']->format('Y-m-d') }}</td>
            <td>{{ $e['or'] }}</td>
            <td>{{ $e['payor'] }}</td>
            <td>{{ $e['user'] }}</td>
            <td>{{ $receipt }}</td>
            <td>{{ $deposit }}</td>
            <td>{{ $balance }}</td>

            @foreach($components as $comp)
                <td>{{ $e['breakdown'][$comp->id] ?? 0 }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
