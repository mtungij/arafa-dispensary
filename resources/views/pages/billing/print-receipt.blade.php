<!DOCTYPE html>
<html class="h-full"
      lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token"
              content="{{ csrf_token() }}">
                <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        <title> {{ config('app.name') }} {{ isset($title) ? '| ' . $title : '' }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
</head>
    
    <body class="bg-neutral-100 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-50">      

    <div 
        class="w-full"
    >
        <div class="bg-white rounded-lg p-6 relative">
            <!-- Close button -->
            <button id="close-receipt" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>

            @if($receiptInvoice)
                @php
                    $company = Auth::user()->company; // Assuming relationship exists
                @endphp

                <!-- Receipt Content Wrapper for Printing -->
                <div id="print-receipt">
                    <!-- HEADER -->
                    <div class="text-center mb-4">
                        @if($company->comp_logo)
                            <img src="{{ asset('storage/'.$company->comp_logo) }}" alt="Company Logo" class="mx-auto h-12 w-12 object-contain mb-1">
                        @endif
                        <h2 class="text-xl font-bold">{{ $company->name }}</h2>
                        <div class="text-sm text-gray-500">
                            {{ $company->email }} | {{ $company->phone }}
                        </div>
                        <hr class="mt-2 border-gray-300">
                    </div>

                    <!-- PATIENT & INVOICE INFO -->
                    <div class="mb-4 text-sm">
                        <div><strong>Invoice #:</strong> {{ $receiptInvoice->id }}</div>
                        <div><strong>Patient:</strong> {{ $receiptInvoice->visit->patient->first_name }} {{ $receiptInvoice->visit->patient->last_name }}</div>
                        <div><strong>Patient ID:</strong> {{ $receiptInvoice->visit->patient->patient_number }}</div>
                        <div><strong>Date:</strong> {{ $receiptInvoice->paid_at ? $receiptInvoice->paid_at->format('d M Y H:i') : now()->format('d M Y H:i') }}</div>
                    </div>

                    <!-- INVOICE ITEMS -->
                    <table class="w-full text-sm border-t border-b border-gray-200 mb-4">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Type</th>
                                <th class="p-2 text-left">Description</th>
                                <th class="p-2 text-right">Qty</th>
                                <th class="p-2 text-right">Unit</th>
                                <th class="p-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receiptInvoice->items as $item)
                                <tr class="border-b border-gray-100">
                                    <td>{{ ucfirst($item->type) }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-right">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- TOTAL & PAYMENT METHOD -->
                    <div class="flex justify-between font-bold text-gray-700 mb-2">
                        <span>Total Paid:</span>
                        <span>{{ number_format($receiptInvoice->items->sum('total'), 2) }} TZS</span>
                    </div>

                    <div class="text-sm mb-4">
                        <strong>Payment Method:</strong> {{ $receiptInvoice->payments->first()?->method ?? 'Cash' }}
                    </div>

                    <div class="text-center text-xs text-gray-500">
                        Thank you for your payment.
                    </div>
                </div>
            @endif
        </div>
    </div>

 @livewireScriptConfig
    {{-- without this it cause flicker when multiple components changes in isolation in the  page --}}
    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
               window.location.href = "{{ route('billing.index') }}";
            };
        }
        loadDarkMode()
    </script>
    <x-ui.toast :maxToasts="1" />
    </body>

</html>