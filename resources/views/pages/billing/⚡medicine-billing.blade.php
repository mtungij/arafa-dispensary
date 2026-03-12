<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Invoice;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $selectedInvoice = null;

   public function getInvoicesProperty()
{
    return Invoice::with([
        'visit.patient',
        'items' => function ($q) {
            $q->where('type', 'medicine');
        }
    ])
    ->whereHas('items', function ($q) {
        $q->where('type', 'medicine');
    })
    ->latest()
    ->get();
}

    public function confirmDispense($invoiceId)
    {
        DB::transaction(function () use ($invoiceId) {

            $invoice = Invoice::with('items')->findOrFail($invoiceId);

            foreach ($invoice->items as $item) {

                $medicine = Medicine::where('name', $item->description)->first();

                if ($medicine) {
                    $medicine->decrement('quantity', $item->quantity);
                }
            }

            $invoice->update([
                'status' => 'dispensed'
            ]);
        });

        $this->dispatch('print-receipt', invoiceId: $invoiceId);
    }
};
?>

<div>
  <table class="w-full text-sm">

<thead>
<tr>
<th>Invoice</th>
<th>Patient</th>
<th>Medicines</th>
<th>Total</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@foreach($this->invoices as $invoice)

<tr class="border-b">

<td>#{{ $invoice->id }}</td>

<td>
{{ $invoice->visit->patient->first_name }}
</td>

<td>

@foreach($invoice->items as $item)

<div class="text-xs border rounded p-1 mb-1">

<strong>{{ $item->description }}</strong><br>

Qty: {{ $item->quantity }}

@if($item->dosage)
<br>Dosage: {{ $item->dosage }}
@endif

@if($item->frequency)
<br>Freq: {{ $item->frequency }}
@endif

@if($item->duration)
<br>Duration: {{ $item->duration }}
@endif

</div>

@endforeach

</td>

<td>
{{ number_format($invoice->total,2) }}
</td>

<td>

<td>
<a
href="{{ route('pharmacy.dispense', $invoice->id) }}"
class="bg-blue-600 text-white px-3 py-1 rounded">

Dispense

</a>
</td>

</td>

</tr>

@endforeach

</tbody>

</table>
</div>
<script>
window.addEventListener('print-receipt', event => {

    let id = event.detail.invoiceId;

    window.open('/pharmacy/print/' + id, '_blank');

});
</script>