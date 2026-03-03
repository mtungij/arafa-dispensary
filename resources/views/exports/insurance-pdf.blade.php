<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Insurance Covered Registration Fees</title>
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
        <div>Insurance Covered Registration Fees Report</div>
        <div>Generated on: {{ $generatedAt->format('d M Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Patient</th>
                <th>MRN</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $index => $invoice)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $invoice->visit->patient->first_name }} {{ $invoice->visit->patient->last_name }}</td>
                    <td>{{ $invoice->visit->patient->patient_number }}</td>
                    <td>{{ number_format($invoice->insurance_amount,2) }}</td>
                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total Amount: {{ number_format($invoices->sum('insurance_amount'),2) }}
    </div>
</body>
</html>