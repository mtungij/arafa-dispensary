<h1>Sold Medicines Report</h1>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Patient</th>
            <th>Medicine</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
            <th>Sold At</th>
            <th>Sold By</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>#{{ $item->invoice_id }}</td>
                <td>{{ $item->invoice->visit->patient->first_name ?? '-' }} {{ $item->invoice->visit->patient->last_name ?? '' }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $item->invoice->user->name ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>