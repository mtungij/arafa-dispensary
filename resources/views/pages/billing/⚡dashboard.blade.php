<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\PatientMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $patients = [];
    public $receiptInvoice = null;
    public $selectedTab = 'overview';

    public function mount()
    {
        $this->loadPatients();
    }

    public function loadPatients()
    {
        $this->patients = Visit::with('patient', 'invoices.items', 'invoices.payments')
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'waiting_payment')
            ->where('current_department', 'billing')
            ->get();
    }

    public function getLatestConsultationInvoice($visit)
    {
        $visit->load('invoices.items');

        return $visit->invoices
            ->sortByDesc('created_at')
            ->first(fn($inv) => $inv->items->isNotEmpty());
    }

    public function confirmPayment($invoiceId)
    {
        DB::transaction(function () use ($invoiceId) {

            $invoice = Invoice::with('visit.patient', 'payments', 'items')
                ->findOrFail($invoiceId);

            

            // 1️⃣ Create Payment Record
            $invoice->payments()->create([
                'company_id'  => Auth::user()->company_id,
                'amount'      => $invoice->patient_amount,
                'method'      => 'cash',
                'received_by' => Auth::id(),
            ]);

            // 2️⃣ Mark Invoice Paid
            $invoice->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            // 3️⃣ Update Investigation Requests (so lab sees them)
           // 3️⃣ Update Investigation Requests (so lab sees them)
foreach ($invoice->items as $item) {
    if ($item->type === 'investigation') {
        $investReq = $item->investigationRequest()->first(); // get actual model

        if ($investReq) {
            // Update existing request
            $investReq->update([
                'status' => 'requested',
            ]);
        } else {
            // Create new InvestigationRequest if missing
            \App\Models\InvestigationRequest::create([
                'visit_id' => $invoice->visit->id,
                'investigation_id' => $item->investigation_id,
                'price' => $item->unit_price,
                'status' => 'requested',
            ]);
        }
    }
}

            // 4️⃣ Detect Workflow Based On Invoice Item Types
            $types = $invoice->items->pluck('type')->unique();

            if ($types->contains('consultation') || $types->contains('investigation')) {
                $toDepartment = 'lab';
                $status = 'waiting_lab';
            } elseif ($types->contains('registration') || $types->contains('medicine')) {
                $toDepartment = 'doctor';
                $status = 'waiting_doctor';
            } else {
                $toDepartment = 'doctor';
                $status = 'waiting_doctor';
            }

            // 5️⃣ Update Visit Department & Status
            $invoice->visit->update([
                'status' => $status,
                'current_department' => $toDepartment,
            ]);

            // 6️⃣ Record Movement
            PatientMovement::create([
                'visit_id' => $invoice->visit->id,
                'from_department' => 'billing',
                'to_department' => $toDepartment,
                'moved_at' => now(),
            ]);

            // 7️⃣ Set receipt for modal
            $this->receiptInvoice = $invoice;

            $this->dispatch('open-receipt-modal');
        });

        // 8️⃣ Reload patients for table
        $this->loadPatients();

        session()->flash('message', 'Payment confirmed successfully.');

        $this->dispatch('refreshDoctorQueue');
        $this->dispatch('refreshLabQueue');
    }

    public function resetReceipt()
    {
        $this->receiptInvoice = null;
    }

    public function printReceipt()
{
    $this->dispatch('print-receipt');
}
}

?>

<div>


   <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Outlined (default)</x-ui.heading>
            <x-ui.tabs wire:model="selectedTab" variant="outlined">
                <x-ui.tab.group>
                    <x-ui.tab name="overview" label="Registration fee" icon="eye" />
                    <x-ui.tab name="analytics" label="Analytics" icon="chart-bar" />
                    <x-ui.tab name="reports" label="Reports" icon="document-chart-bar" />
                    
                </x-ui.tab.group>

                <x-ui.tab.panel name="overview">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.heading level="h4" size="sm">Overview Panel</x-ui.heading>
                        <x-ui.text class="mt-2 opacity-60">
                            This is the overview tab content. Tabs support Livewire wire:model binding.
                        </x-ui.text>
                    </div>
                </x-ui.tab.panel>

                <x-ui.tab.panel name="analytics">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.heading level="h4" size="sm">Analytics Panel</x-ui.heading>
                        <x-ui.text class="mt-2 opacity-60">
                            View your analytics data and insights here.
                        </x-ui.text>
                    </div>
                </x-ui.tab.panel>

                <x-ui.tab.panel name="reports">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.heading level="h4" size="sm">Reports Panel</x-ui.heading>
                        <x-ui.text class="mt-2 opacity-60">
                            Generate and download reports from this panel.
                        </x-ui.text>
                    </div>
                </x-ui.tab.panel>
            </x-ui.tabs>
        </div>



<x-ui.heading level="h2" size="lg">Billing & Payment</x-ui.heading>
<x-ui.text class="opacity-60">Confirm registration fees for cash patients and send them to Doctor.</x-ui.text>

@if(session()->has('message'))
    <div class="p-3 bg-green-50 text-green-700 rounded mt-2">{{ session('message') }}</div>
@endif

<table class="w-full text-sm border mt-4">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">Patient</th>
            <th class="p-2 text-left">Amount</th>
            <th class="p-2 text-left">Action</th>
        </tr>
    </thead>
    <tbody>
       @foreach($patients as $visit)
                @php
                    $consultInvoice = $this->getLatestConsultationInvoice($visit);
                    $consultItem = $consultInvoice?->items->where('type', 'consultation')->first();

                    // Fallback: latest item from any invoice
                    $latestInvoice = $visit->invoices->sortByDesc('created_at')->first();
                    $latestItem = $latestInvoice?->items->first();
                @endphp

                <tr class="border-t">
                    {{-- Patient --}}
                    <td class="p-2">
                        {{ $visit->patient->first_name }} {{ $visit->patient->last_name }}
                        ({{ $visit->patient->patient_number }})
                    </td>

                    {{-- Description --}}
                  <td class="p-2">
    @if($latestInvoice && $latestInvoice->items->count())
        <ul class="list-disc list-inside text-sm">
            @foreach($latestInvoice->items as $item)
                <li>{{ $item->description }} {{ number_format($item->unit_price) }}</li>
            @endforeach
        </ul>
    @else
        No Items
    @endif
</td>

                    {{-- Amount --}}
                   <td class="p-2">
    @if($latestInvoice && $latestInvoice->items->count())
        {{ number_format($latestInvoice->items->sum('unit_price'), 2) }}
    @else
        0.00
    @endif
</td>

                    {{-- Type --}}
                    <td class="p-2">
                        @if($latestItem)
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">
                                {{ ucfirst($latestItem->type) }}
                            </span>
                        @endif
                    </td>

                    {{-- Confirm Payment --}}
              <td class="p-2">
    @php
        $latestInvoice = $visit->invoices->sortByDesc('created_at')->first();
    @endphp

    @if($latestInvoice)
        <x-ui.button 
            wire:click="confirmPayment({{ $latestInvoice->id }})"
            wire:loading.attr="disabled"
            wire:target="confirmPayment({{ $latestInvoice->id }})"
            icon="check-circle"
        >
            Confirm Payment
        </x-ui.button>
    @else
        <span class="text-gray-400 text-xs">No Invoice</span>
    @endif
</td>
                </tr>
            @endforeach
    </tbody>
</table>





<div 
    id="receipt-modal"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden"
>
    <div class="bg-white rounded-lg p-6 w-96 relative shadow-lg">
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

            <!-- PRINT BUTTON -->
           <!-- PRINT BUTTON -->
<div class="mt-4 text-center">
    <x-ui.button
        type="button"
        wire:click="printReceipt"
        icon="printer"
        class="w-full"
    >
        Print Receipt
    </x-ui.button>
</div>
        @endif
    </div>
</div>

<script>
    const modal = document.getElementById('receipt-modal');
    const closeBtn = document.getElementById('close-receipt');

    // Open modal when Livewire triggers event
    window.addEventListener('open-receipt-modal', () => {
        modal.classList.remove('hidden');
    });

    // Close modal
    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        Livewire.emit('resetReceipt');
    });

    Livewire.on('resetReceipt', () => {
        modal.classList.add('hidden');
    });

    // PRINT FUNCTION
    function printReceipt() {
        const printContents = document.getElementById('print-receipt').innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); // reload to restore Livewire events
    }
</script>
</div>


</div>