<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $paidInvestigations = [];

    public function mount()
    {
        $this->loadPaidInvestigations();
    }

    public function loadPaidInvestigations()
    {
        $this->paidInvestigations = Visit::with(['patient', 'invoices.items'])
            ->where('company_id', Auth::user()->company_id)
            ->get()
            ->map(function ($visit) {
                // Only paid invoices with lab items
                $paidInvoices = $visit->invoices
                    ->where('status', 'paid')
                    ->map(function ($invoice) {
                        $invoice->labItems = $invoice->items->where('type', 'lab');
                        return $invoice;
                    })
                    ->filter(fn($invoice) => $invoice->labItems->isNotEmpty());

                $visit->paidInvoices = $paidInvoices;
                return $visit;
            })
            ->filter(fn($visit) => $visit->paidInvoices->isNotEmpty());
    }
}
?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-2">Paid Investigations</h2>
    <p class="text-gray-500 mb-4">Only lab/investigation items that have been paid are displayed here.</p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-200 rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left border-b">Patient</th>
                    <th class="p-2 text-left border-b">Invoice ID</th>
                    <th class="p-2 text-left border-b">Investigation</th>
                    <th class="p-2 text-right border-b">Amount (TZS)</th>
                    <th class="p-2 text-left border-b">Payment Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($investigationVisits as $visit)
                    @foreach($visit->invoices->where('status','paid') as $invoice)
                        @foreach($getInvoiceLabItems($invoice) as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2">{{ $visit->patient->first_name }} {{ $visit->patient->last_name }}</td>
                                <td class="p-2">{{ $invoice->id }}</td>
                                <td class="p-2">{{ $item->description }}</td>
                                <td class="p-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="p-2">
                                    @if($isCoveredByInsurance($item))
                                        <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700">Insurance</span>
                                    @else
                                        <span class="px-2 py-1 rounded bg-green-100 text-green-700">Cash</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">No paid investigations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>