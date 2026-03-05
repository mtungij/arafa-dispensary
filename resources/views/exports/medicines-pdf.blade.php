<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Medicine Inventory</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .header { text-align: center; margin-bottom: 10px; }
        .total { margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if($company && $company->comp_logo)
            <img src="{{ public_path('storage/'.$company->comp_logo) }}" width="70">
        @endif
        <h2>{{ $company->name ?? config('app.name') }}</h2>
        <div>Medicine Inventory Report</div>
        <div>Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Buy Price</th>
                <th>Sell Price Cash</th>
                <th>Sell Price Insurance</th>
                <th>Expire Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicines as $index => $med)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $med->name }}</td>
                    <td>{{ ucfirst($med->type) }}</td>
                    <td>{{ $med->category ?? '-' }}</td>
                    <td>{{ $med->quantity }}</td>
                    <td>{{ number_format($med->buy_price, 2) }}</td>
                    <td>{{ $med->sell_price_cash ? number_format($med->sell_price_cash, 2) : '-' }}</td>
                    <td>{{ $med->sell_price_insurance ? number_format($med->sell_price_insurance, 2) : '-' }}</td>
                    <td>{{ $med->expire_date ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Medicines: {{ $medicines->count() }}
    </div>
</body>
</html>