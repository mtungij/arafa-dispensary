<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Visit;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
      public $investigationVisits = [];

    public function mount()
    {
        $this->loadPaidInvestigations();
    }

    public function loadPaidInvestigations()
    {
        // Get all visits in the company that have at least one paid invoice with investigation items
        $this->investigationVisits = Visit::with(['patient', 'invoices.items'])
            ->where('company_id', Auth::user()->company_id)
            ->get()
            ->filter(function ($visit) {
                // Keep visits where at least one invoice is paid and has investigation items
                return $visit->invoices->contains(function ($invoice) {
                    return $invoice->status === 'paid' &&
                           $invoice->items->contains('type', 'investigation');
                });
            });
    }

    // Check if invoice is covered by insurance
    public function isCoveredByInsurance($invoice)
    {
        return $invoice->items->contains(function ($item) {
            return $item->covered_by_insurance; // Assuming your item has this boolean field
        });
    }
};
?>

<div class="p-4">
    <h2 class="text-xl font-bold mb-2">Paid Investigations</h2>
    <p class="text-gray-500 mb-4">Only investigations that have been confirmed (paid) are shown here.</p>

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
                        @foreach($invoice->items->where('type', 'investigation') as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2">{{ $visit->patient->first_name }} {{ $visit->patient->last_name }}</td>
                                <td class="p-2">{{ $invoice->id }}</td>
                                <td class="p-2">{{ $item->description }}</td>
                                <td class="p-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="p-2">
                                    @if($item->covered_by_insurance)
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