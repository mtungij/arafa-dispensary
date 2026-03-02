<?php

use Livewire\Component;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
     public $patients;
    public $selectedPatient = null;
    public $visitType = 'opd';
    public $registrationFee = 1000; // fetch from settings if needed
    public $paymentAmount = 0;

    public function mount()
    {
        // Load all patients for this company
        $this->patients = Patient::where('company_id', Auth::user()->company_id)->get();
    }

    /**
     * Register a visit with registration fee.
     */
    public function registerVisit()
    {
        if (!$this->selectedPatient) {
            $this->addError('selectedPatient', 'Please select a patient.');
            return;
        }

        DB::transaction(function () {

            // 1️⃣ Create visit
            $visit = Visit::create([
                'company_id' => Auth::user()->company_id,
                'patient_id' => $this->selectedPatient,
                'visit_type' => $this->visitType,
                'status' => 'waiting_payment',
                'created_by' => Auth::id(),
            ]);

            // 2️⃣ Create invoice
            $invoice = Invoice::create([
                'company_id' => Auth::user()->company_id,
                'visit_id' => $visit->id,
                'total' => 0,
                'insurance_amount' => 0,
                'patient_amount' => 0,
                'status' => 'unpaid',
            ]);

            // 3️⃣ Add registration fee
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'type' => 'registration',
                'description' => 'Registration Fee',
                'quantity' => 1,
                'unit_price' => $this->registrationFee,
            ]);

            $invoice->recalculateTotals();

            $this->paymentAmount = $invoice->patient_amount;

            session()->flash('message', 'Visit created. Please collect registration fee.');
        });
    }

    /**
     * Pay the registration fee.
     */
    public function payRegistrationFee()
    {
        DB::transaction(function () {

            $invoice = Invoice::whereHas('visit', function($q){
                $q->where('patient_id', $this->selectedPatient)
                  ->where('status', 'waiting_payment');
            })->firstOrFail();

            // Record payment
            $invoice->payments()->create([
                'amount' => $this->paymentAmount,
                'method' => 'cash',
                'received_by' => Auth::id(),
            ]);

            // Mark invoice as paid and update visit
            $invoice->markAsPaid();
            $invoice->visit->update(['status' => 'waiting_doctor']);

            session()->flash('message', 'Registration fee paid. Patient is ready for doctor.');
        });
    }
};
?>

<div class="space-y-4">

    <x-ui.field>
        <x-ui.label>Select Patient</x-ui.label>
        <x-ui.select wire:model="selectedPatient" placeholder="Search patient...">
            @foreach($patients as $patient)
                <x-ui.select.option value="{{ $patient->id }}">
                    {{ $patient->first_name }} {{ $patient->last_name }}
                </x-ui.select.option>
            @endforeach
        </x-ui.select>
        <x-ui.error name="selectedPatient" />
    </x-ui.field>

    <x-ui.field>
        <x-ui.label>Visit Type</x-ui.label>
        <x-ui.select wire:model="visitType">
            <x-ui.select.option value="opd">OPD</x-ui.select.option>
            <x-ui.select.option value="short_stay">12-hour Bed</x-ui.select.option>
        </x-ui.select>
    </x-ui.field>

    <x-ui.button wire:click="registerVisit">
        Create Visit & Generate Registration Fee
    </x-ui.button>

    @if($paymentAmount > 0)
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">
            Registration Fee: <strong>{{ number_format($paymentAmount) }} Tsh</strong>
            <x-ui.button wire:click="payRegistrationFee" class="ml-4">
                Collect Payment
            </x-ui.button>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mt-2 text-green-600">
            {{ session('message') }}
        </div>
    @endif

</div>