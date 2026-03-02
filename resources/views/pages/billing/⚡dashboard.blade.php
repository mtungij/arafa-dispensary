<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\PatientMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.app-sidebar')]  class extends Component
{
  public $patients = [];
    public $selectedInvoiceId = null;
    public $paymentAmount = 0;
    public $receiptInvoice = null;
    public $showReceiptModal = false;

    public function mount()
    {
        $this->loadPatients();
    }

    public function loadPatients()
    {
        $this->patients = Visit::with('patient', 'invoice')
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'waiting_payment')
            ->where('current_department', 'billing')
            ->get();
    }

    // Confirm payment and move to doctor
    public function confirmPayment($invoiceId)
    {
        $this->selectedInvoiceId = $invoiceId;

        DB::transaction(function () {
            $invoice = Invoice::with('visit.patient')->findOrFail($this->selectedInvoiceId);

            // Record payment
            $invoice->payments()->create([
                'company_id'  => Auth::user()->company_id,
                'amount'      => $invoice->patient_amount,
                'method'      => 'cash',
                'received_by' => Auth::id(),
            ]);

            // Mark invoice as paid
            $invoice->status  = 'paid';
            $invoice->paid_at = now();
            $invoice->save();

            // Update visit to doctor
            $invoice->visit->update([
                'status'             => 'waiting_doctor',
                'current_department' => 'doctor',
            ]);

            // Record movement
            PatientMovement::create([
                'visit_id'        => $invoice->visit->id,
                'from_department' => 'billing',
                'to_department'   => 'doctor',
                'moved_at'        => now(),
            ]);

            // Prepare receipt modal
            $this->receiptInvoice = Invoice::with(['visit.patient', 'payments', 'items'])
                ->find($this->selectedInvoiceId);
            $this->showReceiptModal = true;

            // Reset
            $this->selectedInvoiceId = null;
            $this->loadPatients();

            session()->flash('message', 'Payment confirmed. Patient sent to Doctor.');
        });

        $this->dispatch('refreshDoctorQueue');
    }

};
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
                        <x-ui.button wire:click="confirmPayment({{ $visit->invoice->id }})" icon="check-circle">
                            Confirm Payment
                        </x-ui.button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Receipt Modal --}}
  
        <x-ui.modal id="receipt-modal" heading="Receipt" width="md" wire:model="showReceiptModal">
        @if($receiptInvoice)
            <div class="space-y-2">
                <div><strong>Patient:</strong> {{ $receiptInvoice->visit->patient->first_name }} {{ $receiptInvoice->visit->patient->last_name }}</div>
                <div><strong>Invoice ID:</strong> {{ $receiptInvoice->id }}</div>
                <div><strong>Total Paid:</strong> {{ number_format($receiptInvoice->payments->sum('amount'), 2) }}</div>
                <div><strong>Payment Method:</strong> Cash</div>
            </div>
        @endif
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="$data.showReceiptModal = false">Close</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>