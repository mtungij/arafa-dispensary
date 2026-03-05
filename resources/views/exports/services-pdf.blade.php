<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Inventory</title>
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
            <img src="{{ public_path('storage/' . $company->comp_logo) }}" width="70">
        @endif
        <h2>{{ $company->name ?? config('app.name') }}</h2>
        <div>Service Inventory Report</div>
        <div>Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Cash Price</th>
                <th>Insurance Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $index => $service)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $service->name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $service->type)) }}</td>
                    <td>{{ number_format($service->cash_price, 2) }}</td>
                    <td>{{ number_format($service->insurance_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Services: {{ $services->count() }}
    </div>
</body>
</html>