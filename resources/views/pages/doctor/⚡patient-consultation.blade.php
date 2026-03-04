<?php

use Livewire\Component;
use App\Models\Visit;
use App\Models\Investigation;
use App\Models\InvestigationRequest;
use App\Models\InvoiceItem;
use App\Models\PatientMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $visit;
    public $visitId;

    public $chief_complaint;
    public $past_medical_history;
    public $family_history;
    public $social_history;
    public $rvs;
    public $examination;

    public $activeTab = 'clinical';
    public $selectedInvestigation = null; // dropdown selection
    public $cart = []; // cart array

    public function mount($visitId)
    {
        $this->visitId = $visitId;
        $this->visit = Visit::with(['invoice', 'patient', 'movements'])
            ->findOrFail($visitId);

        $this->chief_complaint      = $this->visit->chief_complaint;
        $this->past_medical_history = $this->visit->past_medical_history;
        $this->family_history       = $this->visit->family_history;
        $this->social_history       = $this->visit->social_history;
        $this->rvs                  = $this->visit->rvs;
        $this->examination          = $this->visit->examination;
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
};
?>



<div class="p-6 bg-gray-100 min-h-screen">

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