<?php

namespace App\Http\Livewire;

use App\Models\Medicine;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MedicinesExport;
use Mpdf\Mpdf;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithPagination;

    public $search = '';

    // Fields for adding new medicine
    public $newName;
    public $newCategory;
    public $newQuantity;
    public $newBuyPrice;
    public $newSellPriceCash;
    public $newSellPriceInsurance;
    public $newExpireDate;
    public $newType = 'private';

    // Fields for editing
    public $editIndex;
    public $editId;
    public $editName;
    public $editCategory;
    public $editQuantity;
    public $editBuyPrice;
    public $editSellPriceCash;
    public $editSellPriceInsurance;
    public $editExpireDate;
    public $editType;

    public $filterCategory = '';
public $filterType = '';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch()
    {
        $this->resetPage();
    }

  public function getMedicinesProperty()
{
    return $this->getFilteredMedicinesQuery()->paginate(10);
}
public function exportExcel()
{
    return Excel::download(new MedicinesExport($this->filterCategory, $this->filterType), 'medicines.xlsx');
}
    public function getFilteredMedicinesQuery()
{
    $query = Medicine::where('company_id', auth()->user()->company_id);

    if($this->search) {
        $query->where('name', 'like', '%' . $this->search . '%');
    }

    if($this->filterCategory) {
        $query->where('category', $this->filterCategory);
    }

    if($this->filterType) {
        $query->where('type', $this->filterType);
    }

    return $query->orderBy('name')->orderBy('type');
}

    // Open edit modal
    public function openEdit($index)
    {
        $medicine = $this->medicines[$index];

        $this->editIndex = $index;
        $this->editId = $medicine->id;
        $this->editName = $medicine->name;
        $this->editCategory = $medicine->category;
        $this->editQuantity = $medicine->quantity;
        $this->editBuyPrice = $medicine->buy_price;
        $this->editSellPriceCash = $medicine->sell_price_cash;
        $this->editSellPriceInsurance = $medicine->sell_price_insurance;
        $this->editExpireDate = $medicine->expire_date;
        $this->editType = $medicine->type;
    }

    // Save edited medicine
    public function saveEdit()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editCategory' => 'nullable|string|max:255',
            'editQuantity' => 'required|integer|min:0',
            'editBuyPrice' => 'required|numeric|min:0',
            'editSellPriceCash' => 'nullable|numeric|min:0',
            'editSellPriceInsurance' => 'nullable|numeric|min:0',
            'editExpireDate' => 'nullable|date',
            'editType' => 'required|in:private,insurance'
        ]);

        $medicine = Medicine::find($this->editId);
        if ($medicine && $medicine->company_id === auth()->user()->company_id) {
            $medicine->update([
                'name' => $this->editName,
                'category' => $this->editCategory,
                'quantity' => $this->editQuantity,
                'buy_price' => $this->editBuyPrice,
                'sell_price_cash' => $this->editType === 'private' ? $this->editSellPriceCash : null,
                'sell_price_insurance' => $this->editType === 'insurance' ? $this->editSellPriceInsurance : null,
                'expire_date' => $this->editExpireDate,
                'type' => $this->editType,
            ]);

            session()->flash('message', 'Medicine updated successfully.');
        }
    }

    // Delete medicine
    public function delete($index)
    {
        $medicine = $this->medicines[$index];

        if ($medicine && $medicine->company_id === auth()->user()->company_id) {
            $medicine->delete();
            session()->flash('message', 'Medicine deleted successfully.');
        }
    }

    // Add new medicine
    public function addMedicine()
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newCategory' => 'nullable|string|max:255',
            'newQuantity' => 'required|integer|min:0',
            'newBuyPrice' => 'required|numeric|min:0',
            'newSellPriceCash' => 'nullable|numeric|min:0',
            'newSellPriceInsurance' => 'nullable|numeric|min:0',
            'newExpireDate' => 'nullable|date',
            'newType' => 'required|in:private,insurance'
        ]);

        Medicine::create([
            'company_id' => auth()->user()->company_id,
            'name' => $this->newName,
            'category' => $this->newCategory,
            'quantity' => $this->newQuantity,
            'buy_price' => $this->newBuyPrice,
            'sell_price_cash' => $this->newType === 'private' ? $this->newSellPriceCash : null,
            'sell_price_insurance' => $this->newType === 'insurance' ? $this->newSellPriceInsurance : null,
            'expire_date' => $this->newExpireDate,
            'type' => $this->newType,
        ]);

        session()->flash('message', 'Medicine added successfully.');

        $this->reset(['newName','newCategory','newQuantity','newBuyPrice','newSellPriceCash','newSellPriceInsurance','newExpireDate','newType']);
        $this->newType = 'private';
    }

public function exportPdf()
{
    $medicines = $this->getFilteredMedicinesQuery()->get();
    $company = auth()->user()->company; // Assuming user has a company relation
    $generatedAt = now();

    $html = view('exports.medicines-pdf', compact('medicines','company','generatedAt'))->render();

    $mpdf = new \Mpdf\Mpdf();

    // Watermark
    if ($company && $company->comp_logo && file_exists(public_path('storage/'.$company->comp_logo))) {
        $mpdf->SetWatermarkImage(public_path('storage/'.$company->comp_logo));
        $mpdf->showWatermarkImage = true;
    } elseif ($company && $company->name) {
        $mpdf->SetWatermarkText($company->name);
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1; // transparency
    }

    $mpdf->WriteHTML($html);

    return response()->streamDownload(function() use ($mpdf) {
        echo $mpdf->Output('', 'S');
    }, 'medicines.pdf');
}
}
?>

<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <x-ui.heading level="h1" size="xl">Medicine Inventory</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            Manage your medicines stock.
        </x-ui.text>
    </div>

    {{-- Toolbar: Search + Add Button --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search medicines..."
                icon="magnifying-glass"
            />
        </div>

        {{-- Add Medicine Modal --}}
        <x-ui.modal
            id="add-medicine-modal"
            heading="Add Medicine"
            description="Create a new medicine record."
            width="md"
        >
            <x-slot:trigger>
                <x-ui.button icon="plus">
                    Add Medicine
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>Medicine Name</x-ui.label>
                    <x-ui.input wire:model="newName" placeholder="e.g. Panadol" />
                </x-ui.field>

             

               <x-ui.field>
    <x-ui.label>Category</x-ui.label>
    <x-ui.select
        placeholder="Select category..."
        icon="map-pin"
        searchable
        wire:model="newCategory"
    >
        <x-ui.select.option value="">-- Select Category --</x-ui.select.option>
        <x-ui.select.option value="analgesics">Analgesics (Pain Relievers)</x-ui.select.option>
        <x-ui.select.option value="antibiotics">Antibiotics</x-ui.select.option>
        <x-ui.select.option value="antivirals">Antivirals</x-ui.select.option>
        <x-ui.select.option value="antifungals">Antifungals</x-ui.select.option>
        <x-ui.select.option value="antiparasitics">Antiparasitics</x-ui.select.option>
        <x-ui.select.option value="cardiovascular">Cardiovascular Drugs</x-ui.select.option>
        <x-ui.select.option value="gastrointestinal">Gastrointestinal Drugs</x-ui.select.option>
        <x-ui.select.option value="respiratory">Respiratory Drugs</x-ui.select.option>
        <x-ui.select.option value="endocrine">Endocrine & Metabolic Drugs</x-ui.select.option>
        <x-ui.select.option value="neurological">Neurological & Psychiatric Drugs</x-ui.select.option>
        <x-ui.select.option value="dermatological">Dermatological Drugs</x-ui.select.option>
        <x-ui.select.option value="vitamins">Vitamins & Supplements</x-ui.select.option>
        <x-ui.select.option value="immunological">Immunological & Vaccines</x-ui.select.option>
        <x-ui.select.option value="hematological">Hematological Drugs</x-ui.select.option>
    </x-ui.select>
</x-ui.field>
                <x-ui.field>
                    <x-ui.label>Quantity</x-ui.label>
                    <x-ui.input wire:model="newQuantity" type="number" step="1" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Buy Price</x-ui.label>
                    <x-ui.input wire:model="newBuyPrice" type="number" step="0.01" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Type</x-ui.label>
                    <x-ui.select wire:model.live="newType" placeholder="Select type">
                        <x-ui.select.option value="private">Cash</x-ui.select.option>
                        <x-ui.select.option value="insurance">Insurance</x-ui.select.option>
                    </x-ui.select>
                </x-ui.field>

                @if($newType === 'private')
                    <x-ui.field>
                        <x-ui.label>Sell Price (Cash)</x-ui.label>
                        <x-ui.input wire:model="newSellPriceCash" type="number" step="0.01" />
                    </x-ui.field>
                @elseif($newType === 'insurance')
                    <x-ui.field>
                        <x-ui.label>Sell Price (Insurance)</x-ui.label>
                        <x-ui.input wire:model="newSellPriceInsurance" type="number" step="0.01" />
                    </x-ui.field>
                @endif

                <x-ui.field>
                    <x-ui.label>Expire Date</x-ui.label>
                    <x-ui.input wire:model="newExpireDate" type="date" />
                </x-ui.field>
            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="addMedicine">Add Medicine</x-ui-button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    <div class="flex gap-2 mb-4">
    <x-ui.select wire:model="filterCategory" placeholder="Filter by Category">
        <x-ui.select.option value="">All Categories</x-ui.select.option>
        @foreach(\App\Models\Medicine::select('category')->distinct()->pluck('category') as $cat)
            <x-ui.select.option value="{{ $cat }}">{{ $cat }}</x-ui.select.option>
        @endforeach
    </x-ui.select>

    <x-ui.select wire:model="filterType" placeholder="Filter by Type">
        <x-ui.select.option value="">All Types</x-ui.select.option>
        <x-ui.select.option value="private">Cash</x-ui.select.option>
        <x-ui.select.option value="insurance">Insurance</x-ui.select.option>
    </x-ui.select>

    <x-ui.button wire:click="exportExcel" icon="arrow-up-tray">Export Excel</x-ui.button>
    <x-ui.button wire:click="exportPdf" icon="arrow-up-tray">Export PDF</x-ui.button>
</div>

    {{-- Medicines Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-neutral-700">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Name</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Type</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Category</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Quantity</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Cash Price</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Insurance Price</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Expire Date</th>
                    <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($this->medicines as $i => $med)
                    <tr class="border-b border-gray-300 last:border-b-0 hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800/40" wire:key="med-{{ $med->id }}">
                        <td class="px-3 py-2 font-medium">{{ $med->name }}</td>
                        <td class="px-3 py-2">{{ ucfirst($med->type) }}</td>
                        <td class="px-3 py-2">{{ $med->category ?? '-' }}</td>
                        <td class="px-3 py-2">
                            @if($med->quantity < 10)
                                <span class="text-red-600 font-semibold">{{ $med->quantity }} (Low)</span>
                            @else
                                {{ $med->quantity }}
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            {{ $med->type === 'private' && $med->sell_price_cash ? number_format($med->sell_price_cash,2) : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $med->type === 'insurance' && $med->sell_price_insurance ? number_format($med->sell_price_insurance,2) : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            @if($med->expire_date && \Carbon\Carbon::parse($med->expire_date)->isPast())
                                <span class="text-red-600 font-semibold">Expired</span>
                            @else
                                {{ $med->expire_date ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right flex justify-end gap-1">
                            {{-- Edit --}}
                            <x-ui.modal
                                id="edit-medicine-modal"
                                heading="Edit Medicine"
                                description="Update the medicine details."
                                width="md"
                            >
                                <x-slot:trigger>
                                    <button class="rounded p-1 text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-800" title="Edit" wire:click="openEdit({{ $i }})">
                                        <x-ui.icon name="pencil-square" class="size-4" />
                                    </button>
                                </x-slot:trigger>

                                <div class="space-y-4">
                                    <x-ui.field>
                                        <x-ui.label>Medicine Name</x-ui.label>
                                        <x-ui.input wire:model="editName" />
                                    </x-ui.field>

                                    <x-ui.field>
                                        <x-ui.label>Category</x-ui.label>
                                        <x-ui.input wire:model="editCategory" />
                                    </x-ui.field>

                                    <x-ui.field>
                                        <x-ui.label>Quantity</x-ui.label>
                                        <x-ui.input wire:model="editQuantity" type="number" step="1" />
                                    </x-ui.field>

                                    <x-ui.field>
                                        <x-ui.label>Buy Price</x-ui.label>
                                        <x-ui.input wire:model="editBuyPrice" type="number" step="0.01" />
                                    </x-ui.field>

                                    <x-ui.field>
                                        <x-ui.label>Type</x-ui.label>
                                        <x-ui.select wire:model="editType">
                                            <x-ui.select.option value="private">Cash</x-ui.select.option>
                                            <x-ui.select.option value="insurance">Insurance</x-ui.select.option>
                                        </x-ui.select>
                                    </x-ui.field>

                                    @if($editType === 'private')
                                        <x-ui.field>
                                            <x-ui.label>Sell Price (Cash)</x-ui.label>
                                            <x-ui.input wire:model="editSellPriceCash" type="number" step="0.01" />
                                        </x-ui.field>
                                    @elseif($editType === 'insurance')
                                        <x-ui.field>
                                            <x-ui.label>Sell Price (Insurance)</x-ui.label>
                                            <x-ui.input wire:model="editSellPriceInsurance" type="number" step="0.01" />
                                        </x-ui.field>
                                    @endif

                                    <x-ui.field>
                                        <x-ui.label>Expire Date</x-ui.label>
                                        <x-ui.input wire:model="editExpireDate" type="date" />
                                    </x-ui.field>
                                </div>

                                <x-slot:footer>
                                    <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                                    <x-ui.button wire:click="saveEdit">Save Changes</x-ui-button>
                                </x-slot:footer>
                            </x-ui.modal>

                            {{-- Delete --}}
                            <x-ui.modal
                                id="delete-medicine-{{ $med->id }}"
                                heading="Delete Medicine"
                                description="This action cannot be undone."
                                icon="exclamation-triangle"
                                width="sm"
                            >
                                <x-slot:trigger>
                                    <button class="rounded p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                                        <x-ui.icon name="trash" class="size-4" />
                                    </button>
                                </x-slot:trigger>

                                <x-ui.text>
                                    Are you sure you want to delete <strong>{{ $med->name }}</strong>?
                                </x-ui.text>

                                <x-slot:footer>
                                    <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                                    <x-ui.button color="red" wire:click="delete({{ $i }})" x-on:click="$data.close()">Delete</x-ui-button>
                                </x-slot:footer>
                            </x-ui.modal>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-neutral-500 dark:text-neutral-400">
                            No medicines found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $this->medicines->links() }}
    </div>
</div>