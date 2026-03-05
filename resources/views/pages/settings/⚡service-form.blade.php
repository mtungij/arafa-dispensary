<?php

use App\Models\Service;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $services; // Collection of Service objects
    public $search = '';

    public $newName, $newType, $newCashPrice, $newInsurancePrice;
    public $editIndex, $editName, $editType, $editCashPrice, $editInsurancePrice;
public $filterType = '';
public $filterCashMin;
public $filterCashMax;
public $filterInsuranceMin;
public $filterInsuranceMax;

    public function mount()
    {
        $this->loadServices();
    }

   public function loadServices()
{
    $companyId = auth()->user()->company_id;

    if (!$companyId) {
        $this->services = collect(); // empty collection
        session()->flash('error', 'Your account does not have a company assigned.');
        return;
    }

    // <-- DO NOT use ->toArray()
    $this->services = Service::where('company_id', $companyId)
        ->orderBy('created_at', 'desc')
        ->get();
}

public function getFilteredServicesProperty()
{
    return $this->services
        ->filter(function($s) {
            $matchesSearch = empty($this->search) || str_contains(strtolower($s->name), strtolower($this->search));

            $matchesType = empty($this->filterType) || $s->type === $this->filterType;

            $matchesCash = true;
            if ($this->filterCashMin !== null) $matchesCash = $matchesCash && $s->cash_price >= $this->filterCashMin;
            if ($this->filterCashMax !== null) $matchesCash = $matchesCash && $s->cash_price <= $this->filterCashMax;

            $matchesInsurance = true;
            if ($this->filterInsuranceMin !== null) $matchesInsurance = $matchesInsurance && $s->insurance_price >= $this->filterInsuranceMin;
            if ($this->filterInsuranceMax !== null) $matchesInsurance = $matchesInsurance && $s->insurance_price <= $this->filterInsuranceMax;

            return $matchesSearch && $matchesType && $matchesCash && $matchesInsurance;
        })
        ->values();
}

    public function addService()
    {
        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            session()->flash('error', 'Cannot add service without a company assigned.');
            return;
        }

        Service::create([
            'company_id' => $companyId,
            'name' => $this->newName,
            'type' => $this->newType,
            'cash_price' => $this->newCashPrice,
            'insurance_price' => $this->newInsurancePrice,
        ]);

        $this->loadServices();
        $this->reset(['newName','newType','newCashPrice','newInsurancePrice']);
    }

    public function openEdit($index)
    {
        $service = $this->filteredServices[$index];

        $this->editIndex = $index;
        $this->editName = $service->name;
        $this->editType = $service->type;
        $this->editCashPrice = $service->cash_price;
        $this->editInsurancePrice = $service->insurance_price;
    }

    public function saveEdit()
    {
        $service = $this->filteredServices[$this->editIndex];

        $service->update([
            'name' => $this->editName,
            'type' => $this->editType,
            'cash_price' => $this->editCashPrice,
            'insurance_price' => $this->editInsurancePrice,
        ]);

        $this->loadServices();
    }

    public function delete($index)
    {
        $service = $this->filteredServices[$index];
        $service->delete();
        $this->loadServices();
    }

public function exportPdf()
{
    // Get filtered services (using your computed property or query)
    $services = $this->filteredServices; // Or use a query if you want DB-level filtering
    $company = auth()->user()->company; // Assuming user has a company relation
    $generatedAt = now();

    // Render Blade view for PDF
    $html = view('exports.services-pdf', compact('services', 'company', 'generatedAt'))->render();

    $mpdf = new \Mpdf\Mpdf();

    // Watermark: logo or company name
    if ($company && $company->comp_logo && file_exists(public_path('storage/' . $company->comp_logo))) {
        $mpdf->SetWatermarkImage(public_path('storage/' . $company->comp_logo));
        $mpdf->showWatermarkImage = true;
    } elseif ($company && $company->name) {
        $mpdf->SetWatermarkText($company->name);
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1; // transparency
    }

    $mpdf->WriteHTML($html);

    return response()->streamDownload(function () use ($mpdf) {
        echo $mpdf->Output('', 'S');
    }, 'services.pdf');
}
}


?>

<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <x-ui.heading level="h1" size="xl">Services</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            Manage your services.
        </x-ui.text>
    </div>

    {{-- Toolbar: Search + Add Button --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search services..."
                icon="magnifying-glass"
            />
        </div>

        {{-- Add Service Modal --}}
        <x-ui.modal
            id="add-service-modal"
            heading="Add Service"
            description="Create a new service."
            width="md"
        >
            <x-slot:trigger>
                <x-ui.button icon="plus">
                    Add Service
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>Service Name</x-ui.label>
                    <x-ui.input wire:model="newName" placeholder="e.g. Bed Rest 12hrs" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Type</x-ui.label>
                    <x-ui.select wire:model="newType" placeholder="Select type">
                        <x-ui.select.option value="procedure">Procedure</x-ui.select.option>
                        <x-ui.select.option value="bed_rest">Bed Rest</x-ui.select.option>
                        <x-ui.select.option value="other">Other</x-ui.select.option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Cash Price</x-ui.label>
                    <x-ui.input wire:model="newCashPrice" type="number" step="0.01" placeholder="0.00" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Insurance Price</x-ui.label>
                    <x-ui.input wire:model="newInsurancePrice" type="number" step="0.01" placeholder="0.00" />
                </x-ui.field>
            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="addService">Add Service</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    <div class="flex gap-2 mb-4 flex-wrap items-center">
    {{-- Filter by Type --}}
    <x-ui.select wire:model="filterType" placeholder="Filter by Type">
        <x-ui.select.option value="">All Types</x-ui.select.option>
        <x-ui.select.option value="procedure">Procedure</x-ui.select.option>
        <x-ui.select.option value="bed_rest">Bed Rest</x-ui.select.option>
        <x-ui.select.option value="other">Other</x-ui.select.option>
    </x-ui.select>


    {{-- Export Buttons --}}
    <x-ui.button wire:click="exportExcel" icon="arrow-up-tray">
        Export Excel
    </x-ui.button>
    <x-ui.button wire:click="exportPdf" icon="arrow-up-tray">
        Export PDF
    </x-ui.button>
</div>  

    {{-- Services Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-neutral-700">
    
      <table class="w-full text-left text-sm">
    <thead>
        <tr class="border-b border-gray-300 bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800/50">
            <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Name</th>
            <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Type</th>
            <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Cash Price</th>
            <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Insurance Price</th>
            <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($this->filteredServices as $i => $service)
            <tr
                class="border-b border-gray-300 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800/40"
                wire:key="service-{{ $service->id }}"
            >
                <td class="px-3 py-2 font-medium text-neutral-900 dark:text-neutral-100">{{ $service->name }}</td>
                <td class="px-3 py-2 text-neutral-500 dark:text-neutral-400">{{ ucfirst(str_replace('_', ' ', $service->type)) }}</td>
                <td class="px-3 py-2 text-right text-neutral-900 dark:text-neutral-100">{{ number_format($service->cash_price) }}</td>
                <td class="px-3 py-2 text-right text-neutral-900 dark:text-neutral-100">{{ number_format($service->insurance_price) }}</td>
                <td class="px-3 py-2 text-right">
                    <div class="flex items-center justify-end gap-1">
                        {{-- Edit --}}
                        <button
                            class="rounded p-1 text-neutral-400 transition-colors hover:bg-gray-100 hover:text-neutral-700 dark:hover:bg-gray-800 dark:hover:text-neutral-200"
                            title="Edit"
                            wire:click="openEdit({{ $i }})"
                            x-on:click.debounce.50ms="$nextTick(() => $modal.open('edit-service-modal'))"
                        >
                            <x-ui.icon name="pencil-square" class="size-4" />
                        </button>

                        {{-- Delete --}}
                        <x-ui.modal
                            id="delete-service-{{ $service->id }}"
                            heading="Delete Service"
                            description="This action cannot be undone."
                            icon="exclamation-triangle"
                            width="sm"
                        >
                            <x-slot:trigger>
                                <button class="rounded p-1 text-neutral-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400" title="Delete">
                                    <x-ui.icon name="trash" class="size-4" />
                                </button>
                            </x-slot:trigger>

                            <x-ui.text>
                                Are you sure you want to delete <strong>{{ $service->name }}</strong>?
                            </x-ui.text>

                            <x-slot:footer>
                                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                                <x-ui.button color="red" wire:click="delete({{ $i }})" x-on:click="$data.close()">Delete</x-ui-button>
                            </x-slot:footer>
                        </x-ui.modal>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-3 py-8 text-center text-neutral-500 dark:text-neutral-400">
                    No services found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
    </div>

    {{-- Edit Service Modal (shared) --}}
    <x-ui.modal
        id="edit-service-modal"
        heading="Edit Service"
        description="Update the service details."
        width="md"
    >
        <div class="space-y-4">
            <x-ui.field>
                <x-ui.label>Service Name</x-ui.label>
                <x-ui.input wire:model="editName" placeholder="e.g. Bed Rest 12hrs" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Type</x-ui.label>
                <x-ui.select wire:model="editType" placeholder="Select type">
                    <x-ui.select.option value="procedure">Procedure</x-ui.select.option>
                    <x-ui.select.option value="bed_rest">Bed Rest</x-ui.select.option>
                    <x-ui.select.option value="other">Other</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Cash Price</x-ui.label>
                <x-ui.input wire:model="editCashPrice" type="number" step="0.01" placeholder="0.00" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Insurance Price</x-ui.label>
                <x-ui.input wire:model="editInsurancePrice" type="number" step="0.01" placeholder="0.00" />
            </x-ui.field>
        </div>

        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
            <x-ui.button wire:click="saveEdit">Save Changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>