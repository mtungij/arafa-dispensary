<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Mpdf\Mpdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport; // We'll create this export class

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = null;
    public $dateTo = null;

    protected $paginationTheme = 'tailwind';

    // Reset page on search
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Computed property for filtered invoices
    public function getInvoicesProperty()
    {
        $companyId = Auth::user()->company_id;

        return Invoice::with('visit.patient')
            ->where('company_id', $companyId)
            ->where('status', 'covered_by_insurance')
            ->when($this->search, function($query) {
                $query->whereHas('visit.patient', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('patient_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(10);
    }

    // Total amount of all filtered invoices
    public function getTotalAmountProperty()
    {
        return $this->invoices->sum('insurance_amount');
    }

    public function exportExcel()
{
    $companyId = Auth::user()->company_id;

    return Excel::download(
        new InvoicesExport($this->search, $this->dateFrom, $this->dateTo, $companyId),
        'insurance-registrations.xlsx'
    );
}

// Export PDF
public function exportPdf()
{
    $company = Auth::user()->company;

    // Fetch invoices with filters
    $companyId = Auth::user()->company_id;

    $invoices = Invoice::with('visit.patient')
        ->where('company_id', $companyId)
        ->where('status', 'covered_by_insurance')
        ->when($this->search, fn($q) => $q->whereHas('visit.patient', fn($q2) =>
            $q2->where('first_name','like','%'.$this->search.'%')
               ->orWhere('last_name','like','%'.$this->search.'%')
               ->orWhere('patient_number','like','%'.$this->search.'%')
        ))
        ->when($this->dateFrom, fn($q) => $q->whereDate('created_at','>=',$this->dateFrom))
        ->when($this->dateTo, fn($q) => $q->whereDate('created_at','<=',$this->dateTo))
        ->latest()
        ->get();

    $html = view('exports.insurance-pdf', [
        'invoices' => $invoices,
        'company' => $company,
        'generatedAt' => now()
    ])->render();

    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 20,
        'margin_bottom' => 15,
    ]);

    if ($company && $company->comp_logo) {
        $mpdf->SetWatermarkImage(public_path('storage/'.$company->comp_logo));
        $mpdf->showWatermarkImage = true;
    }

    $mpdf->WriteHTML($html);

    return response()->streamDownload(function() use ($mpdf) {
        echo $mpdf->Output('', 'S');
    }, 'insurance-registrations.pdf');
}
};
?>

<div class="p-6 space-y-6">

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div class="flex gap-2 items-center">
            <label class="font-semibold text-gray-700">Search Patient:</label>
            <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Name / MRN..." icon="search" />
        </div>

        <div class="flex gap-2 items-end">
            <div>
                <label class="font-semibold text-gray-700">From:</label>
                <x-ui.input type="date" wire:model.live="dateFrom" />
            </div>
            <div>
                <label class="font-semibold text-gray-700">To:</label>
                <x-ui.input type="date" wire:model.live="dateTo" />
            </div>
        </div>
    </div>

    <div class="flex gap-2 mb-4">
    <x-ui.button size="sm" class="bg-green-600 text-white hover:bg-green-700"
        wire:click="exportExcel">
        Export Excel
    </x-ui.button>

    <x-ui.button size="sm" class="bg-red-600 text-white hover:bg-red-700"
        wire:click="exportPdf">
        Export PDF
    </x-ui.button>
</div>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white border rounded-xl shadow-sm">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-2 font-semibold text-gray-600">#</th>
                    <th class="px-4 py-2 font-semibold text-gray-600">Patient</th>
                    <th class="px-4 py-2 font-semibold text-gray-600">MRN</th>
                    <th class="px-4 py-2 font-semibold text-gray-600">Amount</th>
                    <th class="px-4 py-2 font-semibold text-gray-600">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($this->invoices as $index => $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $this->invoices->firstItem() + $index }}</td>
                        <td class="px-4 py-2 uppercase">
                            {{ $invoice->visit->patient->first_name }} {{ $invoice->visit->patient->last_name }}
                        </td>
                        <td class="px-4 py-2">{{ $invoice->visit->patient->patient_number }}</td>
                        <td class="px-4 py-2 font-semibold text-green-600">{{ number_format($invoice->insurance_amount, 2) }}</td>
                        <td class="px-4 py-2">{{ $invoice->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No insurance-covered registration fees found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Total & Pagination --}}
    <div class="flex justify-between items-center mt-4">
        <div class="font-semibold text-gray-700">
            Total Claim: <span class="text-green-600">{{ number_format($this->totalAmount, 2) }}</span>
        </div>
        <div>
            {{ $this->invoices->links() }}
        </div>
    </div>

</div>