<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithPagination;

    public $search = '';
    public $from_date;
    public $to_date;

    public function mount()
    {
        $this->from_date = Carbon::today()->format('Y-m-d');
        $this->to_date = Carbon::today()->format('Y-m-d');
    }

    // Computed property
    public function getSoldMedicinesProperty()
    {
        $query = InvoiceItem::query()
            ->with(['invoice.visit.patient', 'invoice.user'])
            ->where('type', 'medicine');

        // Filter by date
        if ($this->from_date && $this->to_date) {
            $query->whereDate('created_at', '>=', $this->from_date)
                  ->whereDate('created_at', '<=', $this->to_date);
        }

        // Search by patient or medicine description
        if ($this->search) {
            $query->whereHas('invoice.visit.patient', function($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                  ->orWhere('last_name', 'like', '%'.$this->search.'%');
            })->orWhere('description', 'like', '%'.$this->search.'%');
        }

        return $query->orderByDesc('created_at')->paginate(10);
    }

    // Download PDF
    public function downloadPdf()
    {
        $items = InvoiceItem::query()
            ->with(['invoice.visit.patient', 'invoice.user'])
            ->where('type', 'medicine');

        if ($this->from_date && $this->to_date) {
            $items->whereDate('created_at', '>=', $this->from_date)
                  ->whereDate('created_at', '<=', $this->to_date);
        }

        if ($this->search) {
            $items->whereHas('invoice.visit.patient', function($q) {
                $q->where('first_name', 'like', '%'.$this->search.'%')
                  ->orWhere('last_name', 'like', '%'.$this->search.'%');
            })->orWhere('description', 'like', '%'.$this->search.'%');
        }

        $items = $items->get();

        $html = view('exports.sold-medicine-pdf', compact('items'))->render();

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);

        $filePath = storage_path('app/public/sold_medicines.pdf');
        $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
};
?>

<div class="p-6 bg-white rounded shadow space-y-4">

    {{-- Filters --}}
    <div class="flex gap-2">
        <input type="date" wire:model="from_date" class="border rounded px-3 py-2">
        <input type="date" wire:model="to_date" class="border rounded px-3 py-2">
        <input type="text" wire:model="search" placeholder="Search patient or medicine..." class="border rounded px-3 py-2 flex-1">
        <button wire:click="downloadPdf" class="bg-green-600 text-white px-3 py-2 rounded">
            Download PDF
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border border-gray-200 rounded">
          <thead class="bg-gray-50 border-b">
    <tr>
        <th class="px-3 py-2">Invoice #</th>
        <th class="px-3 py-2">Patient</th>
        <th class="px-3 py-2">Medicine</th>
        <th class="px-3 py-2 text-center">Quantity Sold</th>
        <th class="px-3 py-2 text-right">Price</th>
        <th class="px-3 py-2 text-right">Total</th>
        <th class="px-3 py-2">Sold At</th>
        <!-- <th class="px-3 py-2">Sold By</th> -->
        <!-- <th class="px-3 py-2 text-center">Remaining Stock</th>  -->
    </tr>
</thead>
<tbody>
    @forelse($this->soldMedicines as $item)
        <tr class="border-b hover:bg-gray-50">
            <td class="px-3 py-2">#{{ $item->invoice_id }}</td>
            <td class="px-3 py-2">
                {{ $item->invoice->visit->patient->first_name ?? '-' }}
                {{ $item->invoice->visit->patient->last_name ?? '' }}
            </td>
            <td class="px-3 py-2">{{ $item->description }}</td>
            <td class="px-3 py-2 text-center">{{ $item->quantity }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($item->total, 2) }}</td>
            <td class="px-3 py-2">{{ $item->created_at->format('Y-m-d H:i') }}</td>
            <!-- <td class="px-3 py-2">{{ $item->invoice->user->name ?? '-' }}</td> -->
            <!-- <td class="px-3 py-2 text-center">
                {{ $item->medicine->quantity ?? 'N/A' }} <!-- Remaining stock -->
            </td> -->
        </tr>
    @empty
        <tr>
            <td colspan="9" class="px-3 py-4 text-center text-gray-500">
                No medicines sold in this range.
            </td>
        </tr>
    @endforelse
</tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $this->soldMedicines->links() }}
    </div>

</div>