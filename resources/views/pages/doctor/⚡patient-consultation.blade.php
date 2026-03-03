<?php

use Livewire\Component;
use App\Models\Visit;
use App\Models\Investigation;
use App\Models\InvestigationRequest;
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

    public $selectedInvestigations = [];
    public $activeTab = 'clinical';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

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

    /*
    |--------------------------------------------------------------------------
    | Available Investigations (Filtered by Patient Type)
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Selected Total
    |--------------------------------------------------------------------------
    */

    public function getSelectedTotalProperty()
    {
        return Investigation::whereIn('id', $this->selectedInvestigations)
            ->sum('price');
    }

    /*
    |--------------------------------------------------------------------------
    | Save & Send
    |--------------------------------------------------------------------------
    */

    public function saveAndSend()
    {
        $this->validate([
            'chief_complaint' => 'required|string',
            'selectedInvestigations' => 'array',
        ]);

        DB::transaction(function () {

            // Save clinical notes
            $this->visit->update([
                'chief_complaint'      => $this->chief_complaint,
                'past_medical_history' => $this->past_medical_history,
                'family_history'       => $this->family_history,
                'social_history'       => $this->social_history,
                'rvs'                  => $this->rvs,
                'examination'          => $this->examination,
            ]);

            $isCash = $this->visit->patient_type === 'cash';
            $total = 0;

            foreach ($this->selectedInvestigations as $investigationId) {

                // SECURITY: Ensure investigation matches company + category
                $investigation = Investigation::where('company_id', $this->visit->company_id)
                    ->where(
                        'category',
                        $isCash ? 'minor' : 'major'
                    )
                    ->find($investigationId);

                if (!$investigation) {
                    abort(403, 'Invalid investigation selection.');
                }

                InvestigationRequest::create([
                    'visit_id' => $this->visit->id,
                    'investigation_id' => $investigation->id,
                    'price' => $investigation->price,
                    'status' => $isCash ? 'waiting_payment' : 'requested',
                ]);

                $total += $investigation->price;
            }

            if ($total > 0) {

                $invoice = $this->visit->invoice;

                $invoice->increment('total', $total);

                if ($isCash) {
                    $invoice->increment('patient_amount', $total);
                } else {
                    $invoice->increment('insurance_amount', $total);
                }
            }

            // Route patient
            $toDepartment = $isCash ? 'billing' : 'lab';
            $status = $isCash ? 'waiting_payment' : 'waiting_lab';

            $this->visit->update([
                'status' => $status,
                'current_department' => $toDepartment,
            ]);

            PatientMovement::create([
                'visit_id' => $this->visit->id,
                'from_department' => 'doctor',
                'to_department' => $toDepartment,
                'moved_at' => now(),
            ]);
        });

        return redirect()->route('doctor.queue');
    }
};
?>




<div class="p-6 bg-gray-100 min-h-screen">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================= LEFT SIDE - PROFILE CARD ================= --}}
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

                    <p class="text-sm text-gray-500">
                        Visit #{{ $visit->id }}
                    </p>
                </div>

                <div class="mt-6 space-y-2 text-sm text-gray-600">
                    <p><strong>Gender:</strong> {{ $visit->patient->gender }}</p>
                    <p><strong>Phone:</strong> {{ $visit->patient->phone }}</p>
                    <p><strong>Status:</strong> 
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                            {{ $visit->status }}
                        </span>
                    </p>
                  @if($visit->invoice->patient_amount > 0)
    <p>Patient Pays: {{ $visit->invoice->patient_amount }}</p>
@endif

@if($visit->invoice->insurance_amount > 0)
    <p>Insurance Pays: {{ $visit->invoice->insurance_amount }}</p>
@endif
                    <p><strong>Department:</strong> {{ $visit->current_department }}</p>
                </div>
            </div>
        </div>


        {{-- ================= RIGHT SIDE - TABS ================= --}}
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


            {{-- ================= CLINICAL NOTES TAB ================= --}}
            @if($activeTab === 'clinical')
                <div class="space-y-4">

                    <textarea wire:model.lazy="chief_complaint"
                        class="w-full border rounded-lg p-3"
                        rows="2"
                        placeholder="Chief Complaint"></textarea>

                    <textarea wire:model.lazy="past_medical_history"
                        class="w-full border rounded-lg p-3"
                        rows="2"
                        placeholder="Past Medical History"></textarea>

                    <textarea wire:model.lazy="family_history"
                        class="w-full border rounded-lg p-3"
                        rows="2"
                        placeholder="Family History"></textarea>

                    <textarea wire:model.lazy="social_history"
                        class="w-full border rounded-lg p-3"
                        rows="2"
                        placeholder="Social History"></textarea>

                    <textarea wire:model.lazy="rvs"
                        class="w-full border rounded-lg p-3"
                        rows="2"
                        placeholder="RVS"></textarea>

                    <textarea wire:model.lazy="examination"
                        class="w-full border rounded-lg p-3"
                        rows="3"
                        placeholder="Examination"></textarea>

                    <div class="pt-4">
                        <button wire:click="saveAndSend"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow">
                            Save & Send
                        </button>
                    </div>

                </div>
            @endif


            {{-- ================= INVESTIGATIONS TAB ================= --}}
          @if($activeTab === 'investigations')
    <div class="space-y-3">

        @forelse($this->availableInvestigations as $investigation)
            <label class="flex items-center justify-between border rounded-lg p-3 hover:bg-gray-50">

                <div class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $investigation->id }}"
                           wire:model="selectedInvestigations">

                    <span class="font-medium">
                        {{ $investigation->name }}
                    </span>
                </div>

                <span class="text-sm text-gray-600">
                    {{ number_format($investigation->price, 2) }}
                </span>

            </label>
        @empty
            <div class="text-gray-500 text-sm">
                No investigations available for this patient type.
            </div>
        @endforelse

        <div class="mt-4 text-right font-semibold text-lg">
            Total Selected: {{ number_format($this->selectedTotal, 2) }}
        </div>

        <div class="pt-4">
            <button wire:click="saveAndSend"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow">
                Save & Send
            </button>
        </div>

    </div>
@endif

            {{-- ================= VISIT TIMELINE TAB ================= --}}
            @if($activeTab === 'timeline')
                <div class="space-y-4">

                    @foreach($visit->movements as $movement)
                        <div class="border-l-4 border-blue-500 pl-4 py-2 bg-gray-50 rounded">
                            <p class="text-sm font-medium">
                                {{ ucfirst($movement->from_department) }}
                                →
                                {{ ucfirst($movement->to_department) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $movement->moved_at }}
                            </p>
                        </div>
                    @endforeach

                </div>
            @endif

        </div>

    </div>
</div>