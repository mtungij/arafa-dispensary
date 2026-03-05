<?php

use Livewire\Component;
use App\Models\Visit;
use App\Models\Investigation;
use App\Models\InvestigationRequest;
use App\Models\InvoiceItem;
use App\Models\PatientMovement;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithPagination;
    public $visit;
    public $visitId;
public $labResults = []; // [request_id => result text]
    public $chief_complaint;
    public $past_medical_history;
    public $family_history;
    public $social_history;
    public $rvs;
    public $examination;
    public $serviceCart = [];
public $selectedService = null;
public $medicineCart = [];
public $selectedMedicine = null;
public $selectedMedicineQuantity = 1;
public $medicineSearch = '';



    public $activeTab = 'clinical';
    public $selectedInvestigation = null; // dropdown selection
    public $cart = []; // cart array

  public function mount($visitId)
{
    $this->visitId = $visitId;
    $this->visit = Visit::with([
        'invoice',
        'patient',
        'movements',
        'investigationRequests.investigation'
    ])->findOrFail($visitId);

    $this->chief_complaint      = $this->visit->chief_complaint;
    $this->past_medical_history = $this->visit->past_medical_history;
    $this->family_history       = $this->visit->family_history;
    $this->social_history       = $this->visit->social_history;
    $this->rvs                  = $this->visit->rvs;
    $this->examination          = $this->visit->examination;

    $this->loadLabResults();
}

/**
 * Load completed investigations results
 */
public function loadLabResults()
{
    foreach ($this->visit->investigationRequests as $request) {
        if ($request->status === 'completed') {
            $this->labResults[$request->id] = [
                'result' => $request->result,
                'files'  => $request->file_path ? json_decode($request->file_path, true) : []
            ];
        }
    }
}

    // ---------------- Available Investigations ----------------
    public function getAvailableInvestigationsProperty()
    {
        return Investigation::where('company_id', $this->visit->company_id)
            ->where(
                'category',
                $this->visit->invoice->patient_amount > 0 ? 'major' : 'minor'
            )
            ->orderBy('name')
            ->get();
    }




    public function getServicesProperty()
{
    $isCashPatient = $this->visit->invoice->patient_amount > 0;

    return Service::where('company_id', $this->visit->company_id)
        ->get()
        ->map(function ($service) use ($isCashPatient) {
            $service->display_price = $isCashPatient
                ? $service->cash_price
                : $service->insurance_price;
            return $service;
        });
}


public function saveAndSendServiceCart()
{
    if (count($this->serviceCart) === 0) {
        session()->flash('error', 'Service cart is empty!');
        return;
    }

    DB::transaction(function () {
        $visit = $this->visit;
        $isCash = $visit->invoice->patient_amount > 0;

        // Create independent invoice for services
        $serviceInvoice = \App\Models\Invoice::create([
            'company_id'       => $visit->company_id,
            'visit_id'         => $visit->id,
            'total'            => 0,
            'insurance_amount' => 0,
            'patient_amount'   => 0,
            'status'           => $isCash ? 'unpaid' : 'covered_by_insurance',
        ]);

        $total = 0;

        foreach ($this->serviceCart as $serviceId => $item) {
            $service = Service::find($serviceId);
            if (!$service) continue;

            // Create VisitService
            \App\Models\VisitService::create([
                'visit_id'   => $visit->id,
                'service_id' => $service->id,
                'price'      => $item['price'],
            ]);

            // Create InvoiceItem
            \App\Models\InvoiceItem::create([
                'invoice_id'  => $serviceInvoice->id,
                'type'        => 'service',
                'description' => $service->name,
                'quantity'    => 1,
                'unit_price'  => $item['price'],
                'total'       => $item['price'],
            ]);

            $total += $item['price'];
        }

        // Update invoice totals
        if ($total > 0) {
            $serviceInvoice->update([
                'total'            => $total,
                'patient_amount'   => $isCash ? $total : 0,
                'insurance_amount' => $isCash ? 0 : $total,
            ]);
        }

        // Update visit status and movement
        $visit->update([
            'status' => $isCash ? 'waiting_payment' : 'waiting_lab',
            'current_department' => $isCash ? 'billing' : 'lab',
        ]);

        \App\Models\PatientMovement::create([
            'visit_id' => $visit->id,
            'from_department' => 'doctor',
            'to_department'   => $isCash ? 'billing' : 'lab',
            'moved_at'        => now(),
        ]);

        $this->serviceCart = [];
    });

    session()->flash('message', 'Service invoice created successfully!');
}



    // ---------------- Cart Functions ----------------
    public function addToCart()
{
    if (!$this->selectedInvestigation) {
        return;
    }

    if (!in_array($this->selectedInvestigation, $this->cart)) {
        $this->cart[] = (int) $this->selectedInvestigation;
    }

    $this->selectedInvestigation = null;
}

    public function removeFromCart($id)
    {
        $this->cart = array_filter($this->cart, fn($item) => $item != $id);
    }

    public function getCartTotalProperty()
    {
        return Investigation::whereIn('id', $this->cart)->sum('price');
    }

public function saveAndSendCart()
{
    if (count($this->cart) === 0) {
        session()->flash('error', 'Cart is empty!');
        return;
    }

    DB::transaction(function () {
        $visit = $this->visit;

        // Create a new independent invoice for consultation type
        $isCash = $visit->invoice->patient_amount > 0;
        $total = 0;
      

        // Create the independent invoice first
        $consultationInvoice = \App\Models\Invoice::create([
            'company_id'       => $visit->company_id,
            'visit_id'         => $visit->id,
            'total'            => 0, // will increment later
            'insurance_amount' => 0,
            'patient_amount'   => 0,
            'status'           => $isCash ? 'unpaid' : 'covered_by_insurance',
        ]);

        foreach ($this->cart as $investigationId) {
            $inv = Investigation::find($investigationId);
            if (!$inv) continue;

    
            // Create InvestigationRequest
            InvestigationRequest::create([
                'visit_id' => $visit->id,
                'investigation_id' => $inv->id,
                'price' => $inv->price,
                'status' => $isCash ? 'waiting_payment' : 'requested',
            ]);

            // Create InvoiceItem for this independent invoice
            InvoiceItem::create([
                'invoice_id'  => $consultationInvoice->id,
                'type'        => 'consultation', // 👈 mark type
                'description' => $inv->name,
                'quantity'    => 1,
                'unit_price'  => $inv->price,
                'total'       => $inv->price,
            ]);

            $total += $inv->price;
        }

        // Update invoice totals
        if ($total > 0) {
            $consultationInvoice->update([
                'total'            => $total,
                'patient_amount'   => $isCash ? $total : 0,
                'insurance_amount' => $isCash ? 0 : $total,
            ]);
        }

        // Update visit status and movements
        $visit->update([
            'status' => $isCash ? 'waiting_payment' : 'waiting_lab',
            'current_department' => $isCash ? 'billing' : 'lab',
        ]);

        PatientMovement::create([
            'visit_id' => $visit->id,
            'from_department' => 'doctor',
            'to_department' => $isCash ? 'billing' : 'lab',
            'moved_at' => now(),
        ]);

        // Clear cart
        $this->cart = [];
    });

    session()->flash('message', 'Consultation invoice created successfully!');
}

    // ---------------- Tab Switching ----------------
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // ---------------- Clinical Notes Save ----------------
    public function saveAndSend()
    {
        $this->visit->update([
            'chief_complaint' => $this->chief_complaint,
            'past_medical_history' => $this->past_medical_history,
            'family_history' => $this->family_history,
            'social_history' => $this->social_history,
            'rvs' => $this->rvs,
            'examination' => $this->examination,
        ]);

        return redirect()->route('doctor.queue');
    }


    public function addToCartService()
{
    if (!$this->selectedService) return;

    if (!isset($this->serviceCart[$this->selectedService])) {
        $service = Service::find($this->selectedService);
        if (!$service) return;

        $isCashPatient = $this->visit->invoice->patient_amount > 0;
        $this->serviceCart[$service->id] = [
            'name' => $service->name,
            'price' => $isCashPatient ? $service->cash_price : $service->insurance_price,
        ];
    }

    $this->selectedService = null;
}

public function removeServiceFromCart($id)
{
    unset($this->serviceCart[$id]);
}

public function getServiceCartTotalProperty()
{
    return collect($this->serviceCart)->sum('price');
}



// Computed property to display medicines according to patient type
use Livewire\WithPagination;
public function getMedicinesProperty()
{
    $isCashPatient = $this->visit->invoice->patient_amount > 0;

    $query = \App\Models\Medicine::query()
        ->where('company_id', $this->visit->company_id)
        ->where('quantity', '>', 0); // <-- only medicines in stock

    // Apply price condition
    if ($isCashPatient) {
        $query->where('sell_price_cash', '>', 0);
    } else {
        $query->where('sell_price_insurance', '>', 0);
    }

    // Apply search
    if ($this->medicineSearch) {
        $query->where('name', 'like', '%' . $this->medicineSearch . '%');
    }

    return $query
        ->paginate(2)
        ->through(function ($medicine) use ($isCashPatient) {

            $medicine->display_price = $isCashPatient
                ? $medicine->sell_price_cash
                : $medicine->sell_price_insurance;

            return $medicine;
        });
}
public function addToMedicineCart($medicineId = null)
{
    // Use the parameter if passed, otherwise use selectedMedicine
    $medicineId = $medicineId ?? $this->selectedMedicine;

    if (!$medicineId) {
        return;
    }

    $medicine = \App\Models\Medicine::find($medicineId);
    if (!$medicine) return;

    $isCashPatient = $this->visit->invoice->patient_amount > 0;
    $price = $isCashPatient ? $medicine->sell_price_cash : $medicine->sell_price_insurance;

    if ($price <= 0 || $medicine->quantity <= 0) {
        session()->flash('error', 'This medicine cannot be added.');
        return;
    }

    $quantity = $this->selectedMedicineQuantity ?: 1;

    if (!isset($this->medicineCart[$medicine->id])) {
        $this->medicineCart[$medicine->id] = [
            'name'      => $medicine->name,
            'price'     => $price,
            'quantity'  => $quantity,
            'dosage'    => '',
            'frequency' => '',
            'duration'  => '',
        ];
    } else {
        $this->medicineCart[$medicine->id]['quantity'] += $quantity;
    }

    // Reset inputs only if added via select
    if (!$medicineId) {
        $this->selectedMedicine = null;
        $this->selectedMedicineQuantity = 1;
    }
}
// Remove from cart
public function removeMedicineFromCart($id)
{
    unset($this->medicineCart[$id]);
}

// Total
public function getMedicineCartTotalProperty()
{
    return collect($this->medicineCart)->sum(fn($item) => $item['price'] * $item['quantity']);
}

// Save cart to invoice
public function saveAndSendMedicineCart()
{
    if (count($this->medicineCart) === 0) {
        session()->flash('error', 'Medicine cart is empty!');
        return;
    }

    DB::transaction(function () {
        $visit = $this->visit;
        $isCash = $visit->invoice->patient_amount > 0;

        // Create Invoice
        $invoice = \App\Models\Invoice::create([
            'company_id'       => $visit->company_id,
            'visit_id'         => $visit->id,
            'total'            => 0,
            'insurance_amount' => 0,
            'patient_amount'   => 0,
            'status'           => $isCash ? 'unpaid' : 'covered_by_insurance',
        ]);

        $total = 0;

        foreach ($this->medicineCart as $id => $item) {
            $medicine = \App\Models\Medicine::find($id);
            if (!$medicine) continue;

            // Create InvoiceItem
            \App\Models\InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'type'        => 'medicine',
                'description' => $medicine->name,
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['price'],
                'total'       => $item['price'] * $item['quantity'],
                'dosage'      => $item['dosage'] ?? null,
                'frequency'   => $item['frequency'] ?? null,
                'duration'    => $item['duration'] ?? null,
            ]);

            $total += $item['price'] * $item['quantity'];

            // Reduce stock
            $medicine->decrement('quantity', $item['quantity']);
        }

        // Update Invoice totals
        $invoice->update([
            'total'            => $total,
            'patient_amount'   => $isCash ? $total : 0,
            'insurance_amount' => $isCash ? 0 : $total,
        ]);

        // Update Visit status
        $visit->update([
            'status' => $isCash ? 'waiting_payment' : 'medicine',
        ]);

        // Refresh visit property to update UI
        $this->visit = $visit->fresh();

        // Clear cart and inputs
        $this->medicineCart = [];
        $this->selectedMedicine = null;
        $this->selectedMedicineQuantity = 1;
    });

    // Use session flash for Livewire 4 notification
    session()->flash('message', 'Medicine invoice created successfully!');
}
};
?>



<div class="p-6 bg-gray-100 min-h-screen">

@if (session()->has('message'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
        {{ session('message') }}
    </div>
@endif
@if (session()->has('error'))
    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
        {{ session('error') }}
    </div>
@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT PROFILE --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-cyan-500 to-blue-600"></div>
            <div class="px-6 pb-6">
                <div class="flex flex-col items-center -mt-12">
                    <div class="w-24 h-24 rounded-full border-4 border-white shadow-md overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
                             class="w-full h-full object-cover">
                    </div>
                    <h3 class="mt-3 text-xl font-bold text-gray-800">
                        {{ $visit->patient->first_name }} {{ $visit->patient->last_name }}
                    </h3>
                    <p class="text-sm text-gray-500">Visit #{{ $visit->id }}</p>
                </div>

                <div class="mt-6 space-y-2 text-sm text-gray-600">
                    <p><strong>Gender:</strong> {{ $visit->patient->gender }}</p>
                    <p><strong>Phone:</strong> {{ $visit->patient->phone }}</p>
                    <p><strong>Status:</strong> 
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                            {{ $visit->status }}
                        </span>
                    </p>
                @php
    $invoice = $visit->invoice;
@endphp

@if($invoice?->patient_amount > 0)
    <p>CASH Patient</p>
@endif

@if($invoice?->insurance_amount > 0)
    <p>Insurance Patient</p>
@endif
                    <p><strong>Department:</strong> {{ $visit->current_department }}</p>
                </div>
            </div>
        </div>

        {{-- RIGHT TABS --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            {{-- TAB HEADERS --}}
            <div class="flex gap-8 border-b mb-6 text-sm font-medium">
                <button wire:click="setTab('clinical')"
                    class="pb-3 {{ $activeTab === 'clinical' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500' }}">
                    Clinical Notes
                </button>

                <button wire:click="setTab('investigations')"
                    class="pb-3 {{ $activeTab === 'investigations' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500' }}">
                    Investigations
                </button>

                <button wire:click="setTab('medicines')"
    class="pb-3 {{ $activeTab === 'medicines' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500' }}">
    Medicines
</button>

        <button wire:click="setTab('services')" class="pb-3 {{ $activeTab === 'services' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500' }}">
          Services
        </button>

                <button wire:click="setTab('timeline')"
                    class="pb-3 {{ $activeTab === 'timeline' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500' }}">
                    Visit Timeline
                </button>
            </div>

            {{-- CLINICAL TAB --}}
            @if($activeTab === 'clinical')
                <div class="space-y-4">
                    <textarea wire:model.lazy="chief_complaint" class="w-full border rounded-lg p-3" rows="2" placeholder="Chief Complaint"></textarea>
                    <textarea wire:model.lazy="past_medical_history" class="w-full border rounded-lg p-3" rows="2" placeholder="Past Medical History"></textarea>
                    <textarea wire:model.lazy="family_history" class="w-full border rounded-lg p-3" rows="2" placeholder="Family History"></textarea>
                    <textarea wire:model.lazy="social_history" class="w-full border rounded-lg p-3" rows="2" placeholder="Social History"></textarea>
                    <textarea wire:model.lazy="rvs" class="w-full border rounded-lg p-3" rows="2" placeholder="RVS"></textarea>
                    <textarea wire:model.lazy="examination" class="w-full border rounded-lg p-3" rows="3" placeholder="Examination"></textarea>
                    <div class="pt-4">
                        <button wire:click="saveAndSend" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow">
                            Save & Send
                        </button>
                    </div>
                </div>
            @endif

         @if($activeTab === 'investigations')
<div class="space-y-3">

    {{-- Select Investigation --}}
    <div class="flex gap-2 items-end">
        <x-ui.field class="flex-1">
            <x-ui.label>Select Investigation</x-ui.label>
            <x-ui.select 
                placeholder="Find an investigation..."
                icon="beaker"
                searchable
                wire:model="selectedInvestigation"
            >
                @foreach($this->availableInvestigations as $inv)
                    @if(in_array($inv->id, $this->cart))
                        <x-ui.select.option value="{{ $inv->id }}" icon="beaker" disabled>
                            {{ $inv->name }} ({{ number_format($inv->price, 2) }})
                        </x-ui.select.option>
                    @else
                        <x-ui.select.option value="{{ $inv->id }}" icon="beaker">
                            {{ $inv->name }} ({{ number_format($inv->price, 2) }})
                        </x-ui.select.option>
                    @endif
                @endforeach
            </x-ui.select>
        </x-ui.field>

      <button type="button"
    wire:click="addToCart"
    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
    Add
</button>
    </div>

    {{-- Cart Display --}}
@if(count($cart) > 0)
    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

        {{-- Table --}}
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">
                        Investigation
                    </th>
                    <!-- <th class="hidden px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400 sm:table-cell">
                        Category
                    </th> -->
                    <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">
                        Amount (TZS)
                    </th>
                    <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($this->availableInvestigations->whereIn('id', $cart) as $inv)
                    <tr 
                        wire:key="cart-{{ $inv->id }}"
                        class="border-b border-gray-300 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800/40"
                    >
                        {{-- Investigation Name --}}
                        <td class="px-3 py-2 font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $inv->name }}
                        </td>

                        {{-- Category --}}
                        <!-- <td class="hidden px-3 py-2 sm:table-cell">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                {{ ucfirst($inv->category) }}
                            </span>
                        </td> -->

                        {{-- Price --}}
                        <td class="px-3 py-2 text-right font-semibold text-neutral-700 dark:text-neutral-300">
                            {{ number_format($inv->price, 2) }}
                        </td>

                        {{-- Remove --}}
                        <td class="px-3 py-2 text-right">
                            <button 
                                type="button"
                                wire:click="removeFromCart({{ $inv->id }})"
                                class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400"
                            >
                                Remove
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>

            {{-- Total Row --}}
            <tfoot>
                <tr class="bg-gray-50 dark:bg-neutral-800/40">
                    <td colspan="2" class="px-3 py-3 text-right font-semibold text-neutral-700 dark:text-neutral-300">
                        Total
                    </td>
                    <td class="px-3 py-3 text-right text-lg font-bold text-blue-700 dark:text-blue-400">
                        {{ number_format($this->cartTotal, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>

        </table>
    </div>
@endif

    <div class="pt-4">
        <button wire:click="saveAndSendCart" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded" @if(count($cart) === 0) disabled @endif>
            Save & Send Cart
        </button>
    </div>

</div>
@endif

{{-- Lab Results --}}
@if(count($labResults) > 0)
    <div class="mt-8">
        <h4 class="text-lg font-semibold mb-3">Laboratory Results</h4>
        <div class="space-y-4">
            @foreach($visit->investigationRequests as $request)
                @if(isset($labResults[$request->id]))
                    @php $lab = $labResults[$request->id]; @endphp
                    <div class="p-4 border rounded shadow-sm bg-gray-50">
                        <h5 class="font-medium">{{ $request->investigation->name }}</h5>
                        <p class="mt-1"><strong>Result:</strong> {{ $lab['result'] ?? 'N/A' }}</p>

                        @if(!empty($lab['files']))
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($lab['files'] as $file)
                                    @if(Str::endsWith($file, ['jpg','jpeg','png']))
                                        <img src="{{ Storage::url($file) }}" class="h-20 w-auto border rounded">
                                    @elseif(Str::endsWith($file, 'pdf'))
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-blue-600 underline">View PDF</a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif


@if($activeTab === 'services')
    <div class="space-y-3">

        {{-- Select Service --}}
        <div class="flex gap-2 items-end">
            <x-ui.field class="flex-1">
                <x-ui.label>Select Service</x-ui.label>
                <x-ui.select placeholder="Find a service..."
                             wire:model="selectedService"
                             searchable>
                    @foreach($this->services as $service)
                        @if(isset($serviceCart[$service->id]))
                            <x-ui.select.option value="{{ $service->id }}" disabled>
                                {{ $service->name }} ({{ number_format($service->display_price, 2) }})
                            </x-ui.select.option>
                        @else
                            <x-ui.select.option value="{{ $service->id }}">
                                {{ $service->name }} ({{ number_format($service->display_price, 2) }})
                            </x-ui.select.option>
                        @endif
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <button type="button" wire:click="addToCartService" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Add
            </button>
        </div>

        {{-- Service Cart --}}
        @if(count($serviceCart) > 0)
            <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-300 bg-gray-50">
                            <th class="px-3 py-2 font-medium">Service</th>
                            <th class="px-3 py-2 text-right font-medium">Price</th>
                            <th class="px-3 py-2 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceCart as $id => $item)
                            <tr class="border-b border-gray-300 hover:bg-gray-50">
                                <td class="px-3 py-2">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($item['price'], 2) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button wire:click="removeServiceFromCart({{ $id }})" class="bg-red-500 text-white px-2 py-1 rounded">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="1" class="px-3 py-3 text-right font-bold">Total</td>
                            <td class="px-3 py-3 text-right font-bold">{{ number_format($this->serviceCartTotal, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="pt-4">
                <button wire:click="saveAndSendServiceCart" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                    Save & Send Services
                </button>
            </div>
        @endif

    </div>
@endif

@if($activeTab === 'medicines')
<div class="space-y-4">

    {{-- Search --}}
    <input
        type="text"
        wire:model.live="medicineSearch"
        placeholder="Search medicine..."
        class="w-full border rounded px-3 py-2">

    {{-- Medicine List --}}
    <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b bg-gray-50">
                    <th class="px-3 py-2">Medicine</th>
                    <th class="px-3 py-2">Category</th>
                    <th class="px-3 py-2 text-right">Price</th>
                    <th class="px-3 py-2 text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach($this->medicines as $medicine)
                <tr class="border-b hover:bg-gray-50">

                    <td class="px-3 py-2">
                        {{ $medicine->name }}
                        <span class="text-xs text-gray-500">
                            ({{ $medicine->quantity }})
                        </span>
                    </td>

                    <td class="px-3 py-2">
                        {{ $medicine->category ?? '-' }}
                    </td>

                    <td class="px-3 py-2 text-right">
                        {{ number_format($medicine->display_price,2) }}
                    </td>

                    <td class="px-3 py-2 text-right">
                        <button
                            wire:click="addToMedicineCart({{ $medicine->id }})"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">
                            Add
                        </button>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="p-3">
            {{ $this->medicines->links() }}
        </div>
    </div>


    {{-- Prescription Cart --}}
    @if(count($medicineCart) > 0)

    <div class="overflow-x-auto border rounded-lg mt-4">
        <table class="w-full text-sm">

            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-3 py-2">Medicine</th>
                    <th>Qty</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>

            <tbody>

                @foreach($medicineCart as $id => $item)

                <tr class="border-b">

                    <td class="px-3 py-2">
                        {{ $item['name'] }}
                    </td>

                    <td>
                        <input
                            type="number"
                            wire:model.live="medicineCart.{{ $id }}.quantity"
                            class="w-16 border rounded px-1">
                    </td>

                    <td>
                        <input
                            type="text"
                            placeholder="500mg"
                            wire:model.live="medicineCart.{{ $id }}.dosage"
                            class="border rounded px-2 py-1 w-24">
                    </td>

                    <td>
                        <input
                            type="text"
                            placeholder="3x/day"
                            wire:model.live="medicineCart.{{ $id }}.frequency"
                            class="border rounded px-2 py-1 w-24">
                    </td>

                    <td>
                        <input
                            type="text"
                            placeholder="5 days"
                            wire:model.live="medicineCart.{{ $id }}.duration"
                            class="border rounded px-2 py-1 w-24">
                    </td>

                    <td class="text-right">
                        {{ number_format($item['price'] * $item['quantity'],2) }}
                    </td>

                </tr>

                @endforeach

            </tbody>
        </table>
    </div>

    {{-- Save Button --}}
    <div class="pt-4">
        <button
            wire:click="saveAndSendMedicineCart"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
            Save & Send Medicines
        </button>
    </div>

    @endif

</div>
@endif

            {{-- TIMELINE TAB --}}
            @if($activeTab === 'timeline')
                <div class="space-y-4">
                    @foreach($visit->movements as $movement)
                        <div class="border-l-4 border-blue-500 pl-4 py-2 bg-gray-50 rounded">
                            <p class="text-sm font-medium">
                                {{ ucfirst($movement->from_department) }} → {{ ucfirst($movement->to_department) }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $movement->moved_at }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

    </div>
</div>