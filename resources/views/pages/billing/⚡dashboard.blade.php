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
        $this->patients = Visit::with('patient', 'invoice.payments', 'invoice.items')
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'waiting_payment')
            ->where('current_department', 'billing')
            ->get();
    }

    public function confirmPayment($invoiceId)
    {
        DB::transaction(function () use ($invoiceId) {

            $invoice = Invoice::with('visit.patient', 'payments', 'items')->findOrFail($invoiceId);

            $invoice->payments()->create([
                'company_id'  => Auth::user()->company_id,
                'amount'      => $invoice->patient_amount,
                'method'      => 'cash',
                'received_by' => Auth::id(),
            ]);

            $invoice->status = 'paid';
            $invoice->paid_at = now();
            $invoice->save();

            $invoice->visit->update([
                'status' => 'waiting_doctor',
                'current_department' => 'doctor',
            ]);

            PatientMovement::create([
                'visit_id' => $invoice->visit->id,
                'from_department' => 'billing',
                'to_department' => 'doctor',
                'moved_at' => now(),
            ]);

            $this->receiptInvoice = $invoice;

            // Trigger browser event to show modal
            $this->dispatch('open-receipt-modal');
        });

        $this->loadPatients();
        session()->flash('message', 'Payment confirmed. Patient sent to Doctor.');
        $this->dispatch('refreshDoctorQueue');
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
            <tr class="border-t">
                <td class="p-2">
                    {{ $visit->patient->first_name }} {{ $visit->patient->last_name }}
                    ({{ $visit->patient->patient_number }})
                </td>
                <td class="p-2">
                    {{ number_format($visit->invoice->patient_amount, 2) }}
                </td>
                <td class="p-2">
                    <x-ui.button 
                        wire:click="confirmPayment({{ $visit->invoice->id }})"
                        wire:loading.attr="disabled"
                        wire:target="confirmPayment({{ $visit->invoice->id }})"
                        icon="check-circle"
                    >
                        Confirm Payment
                    </x-ui.button>
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
                <div class="space-y-2">
                    <div><strong>Patient:</strong> {{ $receiptInvoice->visit->patient->first_name }} {{ $receiptInvoice->visit->patient->last_name }}</div>
                    <div><strong>Invoice ID:</strong> {{ $receiptInvoice->id }}</div>
                    <div><strong>Total Paid:</strong> {{ number_format($receiptInvoice->payments->sum('amount'), 2) }}</div>
                    <div><strong>Payment Method:</strong> Cash</div>
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