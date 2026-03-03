<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RegistrationFeesExport;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $activeTab = 'paid';
    public $dateFilter = 'all'; // all | today | week | month

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

   public function getInvoicesProperty()
{
    $companyId = Auth::user()->company_id;

    $query = Invoice::with('visit.patient')
        ->where('company_id', $companyId)
        ->where('status', $this->activeTab)
        ->whereHas('visit.patient', function ($q) {
            $q->where('first_name', 'like', '%' . $this->search . '%')
              ->orWhere('last_name', 'like', '%' . $this->search . '%')
              ->orWhere('patient_number', 'like', '%' . $this->search . '%');
        });

    $query = $this->applyDateFilter($query);

    return $query->latest()->paginate(10);
}

    public function getPaidCountProperty()
{
    $companyId = Auth::user()->company_id;

  $query = Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->whereHas('visit.patient', function ($q) {
        $q->where('first_name', 'like', '%' . $this->search . '%')
          ->orWhere('last_name', 'like', '%' . $this->search . '%')
          ->orWhere('patient_number', 'like', '%' . $this->search . '%');
    });

return $this->applyDateFilter($query)->count();

}

public function exportExcel()
{
    $data = $this->filteredQuery()->get();

    return Excel::download(
        new RegistrationFeesExport($data),
        'registration-fees.xlsx'
    );
}

public function getUnpaidCountProperty()
{
    $companyId = Auth::user()->company_id;

    return Invoice::where('company_id', $companyId)
        ->where('status', 'unpaid')
        ->whereHas('visit.patient', function ($q) {
            $q->where('first_name', 'like', '%' . $this->search . '%')
              ->orWhere('last_name', 'like', '%' . $this->search . '%')
              ->orWhere('patient_number', 'like', '%' . $this->search . '%');
        })
        ->count();
}


public function exportPdf()
{
    $data = $this->filteredQuery()->get();

    $dateRange = match ($this->dateFilter) {
        'today' => 'Today (' . now()->format('d M Y') . ')',
        'week'  => 'This Week (' . now()->startOfWeek()->format('d M') .
                    ' - ' . now()->endOfWeek()->format('d M Y') . ')',
        'month' => 'This Month (' . now()->format('F Y') . ')',
        default => 'All Time',
    };

    $company = auth()->user()->company ?? null;
    $watermarkPath = $company && $company->comp_logo
        ? public_path('storage/' . $company->comp_logo)
        : null;

    $html = View::make('exports.registration-fees-pdf', [
        'invoices' => $data,
        'activeTab' => $this->activeTab,
        'dateRange' => $dateRange,
        'watermarkPath' => $watermarkPath,
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
    ]);

    if ($watermarkPath && file_exists($watermarkPath)) {
        $mpdf->SetWatermarkImage($watermarkPath, 0.1, 'F', [210, 297]); // scale=0.1, full-page
        $mpdf->showWatermarkImage = true;
    }

    $mpdf->WriteHTML($html);

    return response()->streamDownload(function () use ($mpdf) {
        echo $mpdf->Output('', 'S');
    }, 'registration-fees-report.pdf');
}

public function getTotalPaidAmountProperty()
{
    $companyId = Auth::user()->company_id;

$query = Invoice::where('company_id', $companyId)
    ->where('status', 'paid')
    ->whereHas('visit.patient', function ($q) {
        $q->where('first_name', 'like', '%' . $this->search . '%')
          ->orWhere('last_name', 'like', '%' . $this->search . '%')
          ->orWhere('patient_number', 'like', '%' . $this->search . '%');
    });

return $this->applyDateFilter($query)->sum('patient_amount');
}
public function updatingDateFilter()
{
    $this->resetPage();
}

private function applyDateFilter($query)
{
    return match ($this->dateFilter) {
        'today' => $query->whereDate('created_at', now()->toDateString()),
        'week'  => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]),
        'month' => $query->whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year),
        default => $query,
    };
}

private function filteredQuery()
{
    $companyId = Auth::user()->company_id;

    $query = Invoice::with('visit.patient')
        ->where('company_id', $companyId)
        ->where('status', $this->activeTab)
        ->whereHas('visit.patient', function ($q) {
            $q->where('first_name', 'like', '%' . $this->search . '%')
              ->orWhere('last_name', 'like', '%' . $this->search . '%')
              ->orWhere('patient_number', 'like', '%' . $this->search . '%');
        });

    return $this->applyDateFilter($query);
}
};
?>
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <x-ui.heading level="h1" size="xl">
            Registration Fees
        </x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            Manage Paid and Unpaid Cash Registration Fees
        </x-ui.text>
    </div>

    {{-- Tabs --}}
  <div class="flex gap-4 border-b pb-2">

    {{-- Paid Tab --}}
    <button
        wire:click="switchTab('paid')"
        class="px-4 py-2 text-sm font-medium rounded-t-lg flex items-center gap-2
        {{ $activeTab === 'paid' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-neutral-700' }}">

        Paid

        <span class="px-2 py-0.5 text-xs rounded-full
            {{ $activeTab === 'paid' ? 'bg-white text-blue-600' : 'bg-blue-600 text-white' }}">
            {{ $this->paidCount }}
        </span>
    </button>

    {{-- Unpaid Tab --}}
    <button
        wire:click="switchTab('unpaid')"
        class="px-4 py-2 text-sm font-medium rounded-t-lg flex items-center gap-2
        {{ $activeTab === 'unpaid' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-neutral-700' }}">

        Unpaid

        <span class="px-2 py-0.5 text-xs rounded-full
            {{ $activeTab === 'unpaid' ? 'bg-white text-red-600' : 'bg-red-600 text-white' }}">
            {{ $this->unpaidCount }}
        </span>
    </button>

    @if($activeTab === 'paid')
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex justify-between items-center">

        <div>
            <p class="text-sm text-green-700 dark:text-green-300">
                Total Cash Registration Fees Collected
            </p>
            <p class="text-2xl font-bold text-green-800 dark:text-green-400">
                TZS {{ number_format($this->totalPaidAmount, 2) }}
            </p>
        </div>
<!-- 
        <div class="text-green-600 dark:text-green-400 text-sm">
            {{ $this->paidCount }} Payments
        </div> -->

    </div>
@endif

</div>

 <div class="flex gap-2 flex-wrap">

    <button wire:click="$set('dateFilter','all')"
        class="px-3 py-1 text-sm rounded-md
        {{ $dateFilter === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-200 dark:bg-neutral-700' }}">
        All
    </button>

    <button wire:click="$set('dateFilter','today')"
        class="px-3 py-1 text-sm rounded-md
        {{ $dateFilter === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-neutral-700' }}">
        Today
    </button>

    <button wire:click="$set('dateFilter','week')"
        class="px-3 py-1 text-sm rounded-md
        {{ $dateFilter === 'week' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-neutral-700' }}">
        This Week
    </button>

    <button wire:click="$set('dateFilter','month')"
        class="px-3 py-1 text-sm rounded-md
        {{ $dateFilter === 'month' ? 'bg-purple-600 text-white' : 'bg-gray-200 dark:bg-neutral-700' }}">
        This Month
    </button>

</div>
<div class="flex gap-2">

    <button wire:click="exportExcel"
        class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">
        Export Excel
    </button>

    <button wire:click="exportPdf"
        class="px-4 py-2 bg-red-600 text-white rounded-md text-sm">
        Export PDF
    </button>

</div>
    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-neutral-700">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b bg-gray-50 dark:bg-neutral-800/50">
                    <th class="px-3 py-2">S/No</th>
                    <th class="px-3 py-2">Patient Name</th>
                    <th class="px-3 py-2">Patient Type</th>
                    <th class="px-3 py-2">Amount</th>
                    <th class="px-3 py-2 text-right">Status</th>
                </tr>
            </thead>
            <tbody>
              @forelse($this->invoices as $index => $invoice)
                    <tr class="border-b hover:bg-gray-50 dark:hover:bg-neutral-800/40">
                        <td class="px-3 py-2">
                           {{ $this->invoices->firstItem() + $index }}
                        </td>

                        <td class="px-3 py-2 font-medium uppercase">
                            {{ $invoice->visit->patient->first_name }}
                            {{ $invoice->visit->patient->last_name }}
                            ({{ $invoice->visit->patient->patient_number }})
                        </td>

                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs uppercase">
                                Cash Patient
                            </span>
                        </td>

                        <td class="px-3 py-2 font-mono">
                            {{ number_format($invoice->patient_amount) }}
                        </td>

                        <td class="px-3 py-2 text-right font-semibold uppercase">
                            @if($activeTab === 'paid')
                                <span class="text-blue-600">
                                    {{ $invoice->paid_at }}
                                </span>
                            @else
                                <span class="text-red-600">
                                    Unpaid
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
       {{ $this->invoices->links() }}
    </div>

</div>






  

