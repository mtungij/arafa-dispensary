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

            // 3️⃣ Detect Workflow Based On Invoice Item Types
            $types = $invoice->items->pluck('type')->unique();

            if ($types->contains('consultation')) {
                $toDepartment = 'lab';
                $status = 'waiting_lab';
            } 
            elseif ($types->contains('registration')) {
                $toDepartment = 'doctor';
                $status = 'waiting_doctor';
            } 
            elseif ($types->contains('medicine')) {
                $toDepartment = 'doctor';
                $status = 'waiting_doctor';
            } 
            else {
                $toDepartment = 'doctor';
                $status = 'waiting_doctor';
            }

            // 4️⃣ Update Visit Department & Status
            $invoice->visit->update([
                'status' => $status,
                'current_department' => $toDepartment,
            ]);

            // 5️⃣ Record Movement
            PatientMovement::create([
                'visit_id' => $invoice->visit->id,
                'from_department' => 'billing',
                'to_department' => $toDepartment,
                'moved_at' => now(),
            ]);

            $this->receiptInvoice = $invoice;

            $this->dispatch('open-receipt-modal');
        });

        $this->loadPatients();

        session()->flash('message', 'Payment confirmed successfully.');

        $this->dispatch('refreshDoctorQueue');
        $this->dispatch('refreshLabQueue');
    }

    public function resetReceipt()
    {
        $this->receiptInvoice = null;
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
    <div class="bg-white rounded-lg p-6 w-96 relative">
        <button id="close-receipt" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>

        @if($receiptInvoice)
            <div class="space-y-4">
                <div class="text-center">
                    <h3 class="font-bold text-lg">Payment Receipt</h3>
                    <div class="text-sm text-gray-500">Invoice #{{ $receiptInvoice->id }}</div>
                    <div class="text-sm text-gray-500">Patient: {{ $receiptInvoice->visit->patient->first_name }} {{ $receiptInvoice->visit->patient->last_name }}</div>
                </div>

                <table class="w-full text-sm border-t border-b border-gray-200">
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

                <div class="flex justify-between font-bold text-gray-700">
                    <span>Total Paid:</span>
                    <span>{{ number_format($receiptInvoice->items->sum('total'), 2) }} TZS</span>
                </div>

                <div>
                    <span class="font-semibold">Payment Method:</span> 
                    {{ $receiptInvoice->payments->first()?->method ?? 'Cash' }}
                </div>
            </div>
        @endif
    </div>
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
        Livewire.emit('resetReceipt'); // reset invoice in component
    });

    // Listen for Livewire reset event
    Livewire.on('resetReceipt', () => {
        modal.classList.add('hidden');
    });
</script>
</div>