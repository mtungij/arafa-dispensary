<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $paidPatients = [];
    public $unpaidPatients = [];

    public function mount()
    {
        $this->loadPatients();
    }

    public function loadPatients()
    {
        $companyId = Auth::user()->company_id;

        // Patients who PAID registration
 $this->paidPatients = Invoice::with('visit.patient')
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->get();

          

        // Patients who DID NOT PAY registration
        $this->unpaidPatients = Invoice::with('visit.patient')
            ->where('company_id', $companyId)
            ->where('status', 'unpaid')
            ->get();
    }

   
};
?>
<div>
   <div class="p-6">

    <h2 class="text-lg font-bold mb-4">Registration Fee Status</h2>

    {{-- PAID PATIENTS --}}
    <div class="mb-8">
        <h3 class="text-green-600 font-semibold mb-2">
            Paid Registration Fee
        </h3>

        <table class="w-full text-sm border">
            <thead class="bg-green-50">
                <tr>
                    <th class="p-2 text-left">Patient</th>
                    <th class="p-2 text-left">Amount</th>
                    <th class="p-2 text-left">Paid At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paidPatients as $invoice)
                    <tr class="border-t">
                        <td class="p-2">
                            {{ $invoice->visit->patient->first_name }}
                            {{ $invoice->visit->patient->last_name }}
                            ({{ $invoice->visit->patient->patient_number }})
                        </td>
                        <td class="p-2">
                            {{ number_format($invoice->patient_amount, 2) }}
                        </td>
                        <td class="p-2">
                            {{ $invoice->paid_at }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-2 text-gray-500">
                            No paid patients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- UNPAID PATIENTS --}}

        {{-- UNPAID PATIENTS --}}
    <div>
        <h3 class="text-red-600 font-semibold mb-2">
            Unpaid Registration Fee
        </h3>

        <table class="w-full text-sm border">
            <thead class="bg-red-50">
                <tr>
                    <th class="p-2 text-left">Patient</th>
                    <th class="p-2 text-left">Amount</th>
                    <th class="p-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unpaidPatients as $invoice)
                    <tr class="border-t">
                        <td class="p-2">
                            {{ $invoice->visit->patient->first_name }}
                            {{ $invoice->visit->patient->last_name }}
                            ({{ $invoice->visit->patient->patient_number }})
                        </td>
                        <td class="p-2">
                            {{ number_format($invoice->patient_amount, 2) }}
                        </td>
                        <td class="p-2 text-red-600 font-medium">
                            Unpaid
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-2 text-gray-500">
                            No unpaid patients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</div>