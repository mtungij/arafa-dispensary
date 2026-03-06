<div class="bg-white rounded shadow p-4">

<h2 class="text-lg font-bold mb-3">Patient</h2>

<div class="mb-3">
<strong>{{ $invoice->visit->patient->first_name }}
{{ $invoice->visit->patient->last_name }}</strong>
<br>
<small>Phone: {{ $invoice->visit->patient->phone }}</small>
</div>
  <p><strong>Type:</strong>
        @if($invoice->patient_amount > 0)
            <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-sm">Cash Patient</span>
        @elseif($invoice->insurance_amount > 0)
            <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-sm">Covered by Insurance</span>
        @else
            <span class="px-2 py-1 rounded bg-gray-100 text-gray-600 text-sm">Unknown</span>
        @endif
    </p>
<hr class="my-3">

<h3 class="font-semibold mb-2">Prescription</h3>

@foreach($invoice->items as $item)

<div class="border rounded p-2 mb-2">

<strong>{{ $item->description }}</strong>

<div class="text-xs text-gray-600">

Qty: {{ $item->quantity }}

@if($item->dosage)
<br>Dosage: {{ $item->dosage }}
@endif

@if($item->frequency)
<br>Frequency: {{ $item->frequency }}
@endif

@if($item->duration)
<br>Duration: {{ $item->duration }}
@endif

</div>

</div>

@endforeach

</div>