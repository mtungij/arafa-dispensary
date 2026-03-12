<?php

use App\Exports\MovementsExport;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\PatientMovement;
use App\Models\RegistrationFee;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $selectedPatient = null;

    public $visitType = 'opd';

    public $patientType = null;

    public $search = '';

    // New patient fields
    public $first_name;

    public $last_name;

    public $phone;

    public $gender;

    public $dob;

    public $dateFrom = null;

    public $dateTo = null;

    public $department = null; // new property

    public $selectedVisitMovements = [];

    /*
    |--------------------------------------------------------------------------
    | COMPUTED PROPERTIES
    |--------------------------------------------------------------------------
    */

    public function getPatientsProperty()
    {
        return Patient::with(['visits.invoice'])
            ->where('company_id', Auth::user()->company_id)
            ->whereDoesntHave('visits', function ($query) {
                $query->whereIn('status', [
                    'waiting_payment',
                    'waiting_doctor',
                    'in_consultation',
                ]);
            })
            ->latest()
            ->get();
    }

    public function getMovementsProperty()
    {
        return PatientMovement::with(['visit.patient', 'visit.invoice'])
            ->whereHas('visit', fn ($q) => $q->where('company_id', Auth::user()->company_id))

            // 🔎 Patient Search Filter
            ->whereHas('visit.patient', function ($q) {
                $q->when($this->search, fn ($query) => $query->where(function ($sub) {
                    $sub->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('patient_number', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                }));
            })

            // 📅 Date Range Filter
            ->when($this->dateFrom, fn ($query) => $query->whereDate('moved_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('moved_at', '<=', $this->dateTo))

            // 🏥 Department Filter
            ->when($this->department, fn ($query) => $query->where(function ($q) {
                $q->where('from_department', $this->department)
                    ->orWhere('to_department', $this->department);
            }))

            // Only latest movement per visit
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('patient_movements')
                    ->groupBy('visit_id');
            })
            ->latest('moved_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE NEW PATIENT
    |--------------------------------------------------------------------------
    */

    public function save()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $patient = Patient::create([
            'company_id' => Auth::user()->company_id,
            'patient_number' => 'MRN-'.now()->format('YmdHis').'-'.rand(100, 999),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'created_by' => Auth::id(),
        ]);

        $this->selectedPatient = $patient->id;

        $this->reset(['first_name', 'last_name', 'phone', 'gender', 'dob']);

        $this->dispatch('close-modal', id: 'create-patient-modal');

        session()->flash('message', 'Patient registered successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER VISIT
    |--------------------------------------------------------------------------
    */

    public function registerVisit()
    {
        $this->validate([
            'selectedPatient' => 'required|exists:patients,id',
            'patientType' => 'required|in:cash,insurance',
            'visitType' => 'required|in:opd,short_stay',
        ]);

        $companyId = Auth::user()->company_id;

        try {

            DB::transaction(function () use ($companyId) {

                // 🚫 Prevent duplicate active visit
                $existingVisit = Visit::where('company_id', $companyId)
                    ->where('patient_id', $this->selectedPatient)
                    ->whereIn('status', [
                        'waiting_payment',
                        'waiting_doctor',
                        'consultation',
                    ])
                    ->lockForUpdate() // 🔐 Prevent race condition
                    ->first();

                // dd($existingVisit);

                if ($existingVisit) {
                    throw new \Exception('Patient already has an active visit.');
                }

                $registrationFee = RegistrationFee::where('company_id', $companyId)
                    ->where('patient_type', $this->patientType)
                    ->firstOrFail();

                $amount = $registrationFee->amount;

                // dd($amount);

                $initialStatus = $this->patientType === 'cash'
                    ? 'waiting_payment'
                    : 'waiting_doctor';

                $initialDepartment = $this->patientType === 'cash'
                    ? 'billing'
                    : 'doctor';

                $visit = Visit::create([
                    'company_id' => $companyId,
                    'patient_id' => $this->selectedPatient,
                    'visit_type' => $this->visitType,
                    'status' => $initialStatus,
                    'current_department' => $initialDepartment,
                    'created_by' => Auth::id(),
                ]);

                $invoice = Invoice::create([
                    'company_id' => $companyId,
                    'visit_id' => $visit->id,
                    'total' => $amount,
                    'insurance_amount' => $this->patientType === 'insurance' ? $amount : 0,
                    'patient_amount' => $this->patientType === 'cash' ? $amount : 0,
                    'status' => $this->patientType === 'cash'
                                            ? 'unpaid'
                                            : 'covered_by_insurance',
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'type' => 'registration',
                    'description' => 'Registration Fee',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total' => $amount,
                ]);

                PatientMovement::create([
                    'visit_id' => $visit->id,
                    'from_department' => 'registration',
                    'to_department' => $initialDepartment,
                    'moved_at' => now(),
                ]);
            });

            $this->reset(['selectedPatient', 'patientType']);

            session()->flash('message', 'Visit registered successfully.');

        } catch (\Exception $e) {

            session()->flash('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW MOVEMENT HISTORY
    |--------------------------------------------------------------------------
    */

    public function viewMovement($visitId)
    {
        $this->selectedVisitMovements = PatientMovement::with('visit.patient')
            ->where('visit_id', $visitId)
            ->orderBy('moved_at')
            ->get();

        $this->dispatch('open-modal', id: 'movement-history-modal');
    }

    public function exportExcel()
    {
        return Excel::download(
            new MovementsExport($this->search, $this->dateFrom, $this->dateTo),
            'patient-movements.xlsx'
        );
    }

    public function exportPdf()
    {
        $movements = $this->movements;

        $user = auth()->user();
        $company = $user->company ?? null;

        $html = view('exports.movements-pdf', [
            'movements' => $movements,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        // ✅ Professional Watermark (Low Opacity)
        if ($company) {
            $mpdf->SetWatermarkText(
                strtoupper($company->name),
                0.05 // LOW opacity (0.03 - 0.08 ideal)
            );

            $mpdf->showWatermarkText = true;
        }

        $mpdf->WriteHTML($html);

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, 'patient-movements.pdf');
    }
};
?>

<div class="space-y-6">

    {{-- =============================== --}}
    {{-- PAGE HEADER --}}
    {{-- =============================== --}}
    <div>
        <x-ui.heading level="h1" size="xl">Patients Registration</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            Register new patients and manage their visits from this dashboard.
        </x-ui.text>
    </div>

    {{-- =============================== --}}
    {{-- PATIENT SELECT + CREATE --}}
    {{-- =============================== --}}
    <div class="flex gap-4 items-end">

        {{-- Select Existing Patient --}}
        <x-ui.field class="flex-1">
            <x-ui.label>Search Existing Patient</x-ui.label>
            <x-ui.select
                wire:key="patient-select-{{ count($this->patients) }}"
                placeholder="Find a patient..."
                icon="magnifying-glass"
                searchable
                wire:model="selectedPatient"
            >
                @foreach($this->patients as $patient)

                    @php
                        $lastVisit = $patient->visits->first();
                        $type = optional($lastVisit?->invoice)->patient_amount > 0
                            ? 'Cash'
                            : 'Insurance';
                    @endphp

                    <x-ui.select.option value="{{ $patient->id }}">
                        {{ $patient->patient_number }} -
                        {{ $patient->first_name }} {{ $patient->last_name }}
                        ({{ $patient->phone }}) 
                    </x-ui.select.option>
                @endforeach
            </x-ui.select>
        </x-ui.field>

        {{-- Create New Patient Modal --}}
        <x-ui.modal id="create-patient-modal" heading="Register New Patient" width="md">
            <x-slot:trigger>
                <x-ui.button icon="plus-circle">
                    Create New Patient
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">

                <x-ui.field>
                    <x-ui.label>First Name</x-ui.label>
                    <x-ui.input wire:model="first_name" />
                    @error('first_name') 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Last Name</x-ui.label>
                    <x-ui.input wire:model="last_name" />
                    @error('last_name') 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Phone</x-ui.label>
                    <x-ui.input wire:model="phone" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Gender</x-ui.label>
                    <select wire:model="gender" class="w-full border rounded p-2">
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Date of Birth</x-ui.label>
                    <x-ui.input type="date" wire:model="dob" />
                </x-ui.field>


            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$dispatch('close-modal', {id: 'create-patient-modal'})">
                    Cancel
                </x-ui.button>
                <x-ui.button wire:click="save">
                    Register Patient
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

    </div>

    @error('selectedPatient')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror

    {{-- =============================== --}}
    {{-- VISIT OPTIONS --}}
    {{-- =============================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <x-ui.field>
            <x-ui.label>Visit Type</x-ui.label>
            <x-ui.select
                placeholder="Choose visit type..."
                icon="arrow-path-rounded-square"
                wire:model="visitType"
            >
                <x-ui.select.option value="opd">OPD</x-ui.select.option>
                <x-ui.select.option value="short_stay">12-Hour Bed</x-ui.select.option>
            </x-ui.select>
        </x-ui.field>

           <x-ui.field>
                <x-ui.label>Patient Type</x-ui.label>
                <x-ui.select
                    placeholder="Select patient type..."
                    icon="user"
                    searchable
                     wire:model="patientType"
                >
                    <x-ui.select.option value="cash">CASH PATIENT</x-ui.select.option>
                    <x-ui.select.option value="insurance">INSURANCE PATIENT</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

    </div>

    {{-- =============================== --}}
    {{-- REGISTER VISIT BUTTON --}}
    {{-- =============================== --}}
    <div>
     <x-ui.button
    wire:click="registerVisit"
    wire:loading.attr="disabled"
    wire:target="registerVisit"
    icon="plus-circle"
>
    Create Visit & Generate Registration Fee
</x-ui.button>
    </div>

    {{-- =============================== --}}
    {{-- MOVEMENT TABLE --}}
    {{-- =============================== --}}
    <div class="mt-8 space-y-6">

        {{-- ===================================== --}}
        {{-- FILTER CARD --}}
        {{-- ===================================== --}}
        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 transition-all">

            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <x-ui.button
                    size="sm"
                    wire:click="exportExcel"
                    class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white flex items-center gap-2"
                >
                    <span>📊</span>
                    Export Excel
                </x-ui.button>

                <x-ui.button
                    size="sm"
                    wire:click="exportPdf"
                    class="bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white flex items-center gap-2"
                >
                    <span>📄</span>
                    Export PDF
                </x-ui.button>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">

                {{-- Left Side: Search --}}
                <div class="w-full lg:w-1/3">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                        🔍 Search Patient
                    </label>

                    <x-ui.input
                        wire:model.live.debounce.500ms="search"
                        placeholder="Name, MRN, or phone..."
                        icon="magnifying-glass"
                        class="bg-white dark:bg-slate-700"
                    />
                </div>

                {{-- Right Side: Filters --}}
                <div class="flex flex-col sm:flex-row gap-4">

                    {{-- From Date --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                            📅 From
                        </label>
                        <input
                            type="date"
                            wire:model.live="dateFrom"
                            class="border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2.5 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        />
                    </div>

                    {{-- To Date --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                            📅 To
                        </label>
                        <input
                            type="date"
                            wire:model.live="dateTo"
                            class="border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2.5 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        />
                    </div>

                    {{-- Department --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2.5">
                            🏥 Department
                        </label>
                        <select
                            wire:model.live="department"
                            class="border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2.5 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        >
                            <option value="">All Departments</option>
                            <option value="registration">Registration</option>
                            <option value="billing">Billing</option>
                            <option value="doctor">Doctor</option>
                            <option value="lab">Lab</option>
                        </select>
                    </div>

                    {{-- Filter Buttons --}}
                    <div class="flex items-end gap-2">
                        <x-ui.button
                            size="sm"
                            variant="outline"
                            wire:click="$set('dateFrom', '{{ now()->toDateString() }}'); $set('dateTo', '{{ now()->toDateString() }}')"
                            class="whitespace-nowrap"
                        >
                            Today
                        </x-ui.button>

                        @if($search || $dateFrom || $dateTo)
                            <x-ui.button
                                size="sm"
                                variant="outline"
                                class="text-red-600 dark:text-red-400 border-red-300 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 whitespace-nowrap"
                                wire:click="$set('search',''); $set('dateFrom', null); $set('dateTo', null);"
                            >
                                ✕ Clear
                            </x-ui.button>
                        @endif
                    </div>
                </div>

            </div>

        </div>


        {{-- ===================================== --}}
        {{-- TABLE CARD --}}
        {{-- ===================================== --}}
    <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden">

        {{-- Table Header --}}
        <div class="overflow-x-auto">
            <table class="w-full">

                <thead class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/30 dark:to-cyan-900/30 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">From</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">To</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-slate-800">

                    @forelse($this->movements as $movement)

                        @php
                            $invoice = $movement->visit->invoice ?? null;
                            $isCash = optional($invoice)->patient_amount > 0;
                        @endphp

                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors duration-200">

                            {{-- Patient Name --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($movement->visit->patient->first_name, 0, 1) . substr($movement->visit->patient->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ $movement->visit->patient->first_name }} {{ $movement->visit->patient->last_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            ID: #{{ $movement->visit->patient->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Payment Type --}}
                            <td class="px-6 py-5">
                                <x-ui.badge
                                    :icon="$isCash ? 'banknotes' : 'shield-check'"
                                    :color="$isCash ? 'blue' : 'emerald'"
                                >
                                    {{ $isCash ? 'Cash' : 'Insurance' }}
                                </x-ui.badge>
                            </td>

                            {{-- Amount --}}
                            <td class="px-6 py-5">
                                <div class="font-bold">
                                    @if($isCash)
                                        <span class="text-gray-900 dark:text-white">TSH {{ number_format($invoice->total ?? 0, 0) }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">
                                            ✓ Covered
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- From Department --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 text-xs font-semibold uppercase">
                                    @if($movement->from_department === 'registration')
                                        📝
                                    @elseif($movement->from_department === 'doctor')
                                        👨‍⚕️
                                    @elseif($movement->from_department === 'lab')
                                        🧪
                                    @elseif($movement->from_department === 'billing')
                                        💰
                                    @else
                                        📍
                                    @endif
                                    {{ $movement->from_department }}
                                </span>
                            </td>

                            {{-- To Department --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg
                                    @if($movement->to_department === 'doctor')
                                        bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                                    @elseif($movement->to_department === 'lab')
                                        bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300
                                    @elseif($movement->to_department === 'billing')
                                        bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                    @else
                                        bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300
                                    @endif
                                    text-xs font-semibold uppercase">
                                    @if($movement->to_department === 'doctor')
                                        🩺
                                    @elseif($movement->to_department === 'lab')
                                        🧪
                                    @elseif($movement->to_department === 'billing')
                                        💳
                                    @else
                                        ➜
                                    @endif
                                    {{ $movement->to_department }}
                                </span>
                            </td>

                            {{-- Time --}}
                            <td class="px-6 py-5">
                                <div class="text-sm">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($movement->moved_at)->format('d M Y') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($movement->moved_at)->format('H:i') }}
                                    </p>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-bold">
                                    <span class="inline-block h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                                    Active
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="px-6 py-5 text-center">
                                <button
                                    wire:click="viewMovement({{ $movement->visit_id }})"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors duration-200"
                                >
                                    <span>📋</span>
                                    View Movements
                                </button>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="text-4xl mb-3">📊</div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium text-lg">No patient movements found</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your filters or register a new patient</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

</div>

    {{-- =============================== --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- =============================== --}}
    @if(session()->has('message'))
        <div class="mt-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-700 dark:text-emerald-300 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✓</span>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif


    {{-- =============================== --}}
    {{-- MOVEMENT HISTORY MODAL --}}
    {{-- =============================== --}}
    <x-ui.modal
        id="movement-history-modal"
        heading="Patient Movement Timeline"
        width="lg"
    >

        @if(count($selectedVisitMovements) > 0)

            @php
                $lastMovement = $selectedVisitMovements->last();
            @endphp

            <div class="space-y-6 relative pl-6 border-l-2 border-gray-200">

                @foreach($selectedVisitMovements as $movement)

                    @php
                        $isCurrent = $movement->id === $lastMovement->id;
                    @endphp

                    <div class="relative">

                        <div class="absolute -left-3 top-1 w-5 h-5 rounded-full bg-blue-500"></div>

                        <div class="ml-4 p-4 rounded-lg border
                            {{ $isCurrent ? 'bg-green-50 border-green-400 shadow-md' : 'bg-white border-gray-200' }}
                        ">

                            <div class="flex justify-between items-center">
                                <div class="font-semibold">
                                    {{ ucfirst($movement->from_department) }}
                                    →
                                    {{ ucfirst($movement->to_department) }}

                                    @if($isCurrent)
                                        <span class="ml-2 text-xs px-2 py-1 bg-green-200 text-green-800 rounded">
                                            Current
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($movement->moved_at)->format('d M Y H:i') }}
                                </div>
                            </div>

                        </div>
                    </div>

                @endforeach

            </div>

        @endif

        <x-slot:footer>
            <x-ui.button
                variant="outline"
                x-on:click="$dispatch('close-modal', {id:'movement-history-modal'})"
            >
                Close
            </x-ui.button>
        </x-slot:footer>

    </x-ui.modal>

</div>