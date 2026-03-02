<?php


use App\Models\Investigation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvestigationsExport;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    use WithPagination;

    public $name;
    public $category = 'minor';
    public $price;
    public $search = '';
    public $editingId = null;
    public $filterCategory = ''; // for filtering category

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|in:minor,major',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function getInvestigationsProperty()
{
    return Investigation::where('company_id', Auth::user()->company_id)
        ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
        ->where('name', 'like', '%' . $this->search . '%')
        ->latest()
        ->paginate(10);
}

    public function create()
    {
        $this->resetForm();
    }

    public function edit($id)
    {
        $investigation = Investigation::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $this->editingId = $investigation->id;
        $this->name = $investigation->name;
        $this->category = $investigation->category;
        $this->price = $investigation->price;

        // Tell Alpine modal to open
        $this->dispatch('open-modal', id: 'investigation-modal');
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            Investigation::where('id', $this->editingId)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'name' => $this->name,
                    'category' => $this->category,
                    'price' => $this->price,
                ]);

            session()->flash('message', 'Investigation updated successfully.');
        } else {
            Investigation::create([
                'company_id' => Auth::user()->company_id,
                'name' => $this->name,
                'category' => $this->category,
                'price' => $this->price,
            ]);

            session()->flash('message', 'Investigation added successfully.');
        }

        $this->resetForm();

        // Close modal after save
        $this->dispatch('close-modal', id: 'investigation-modal');
    }

    public function delete($id)
    {
        Investigation::where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->delete();

        session()->flash('message', 'Investigation deleted successfully.');
    }

    private function resetForm()
    {
        $this->reset(['name', 'price', 'editingId']);
        $this->category = 'minor';
        $this->resetValidation();
    }

    public function exportExcel()
{
    return Excel::download(new InvestigationsExport($this->filterCategory), 'investigations.xlsx');
}

public function exportPDF()
{
   $company = auth()->user()->company ?? null;

    $investigations = Investigation::where('company_id', auth()->user()->company_id)
        ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
        ->latest()
        ->get();

    $html = view('exports.investigations-pdf', [
        'investigations' => $investigations,
        'company' => $company,
        'category' => $this->filterCategory
    ])->render();

    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15,
    ]);

    // ✅ Add professional watermark using mPDF
    if ($company) {
        $mpdf->SetWatermarkText(strtoupper($company->name), 0.05); // 5% opacity
        $mpdf->showWatermarkText = true;
    }

    $mpdf->WriteHTML($html);

    return response()->streamDownload(function () use ($mpdf) {
        echo $mpdf->Output('', 'S');
    }, 'investigations.pdf');
}
};
?>

<div class="p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Investigations</h2>
    </div>

    @if(session()->has('message'))
        <div class="bg-green-100 text-green-800 p-3 rounded">
            {{ session('message') }}
        </div>
    @endif

 <div class="flex flex-wrap gap-4 mb-4 items-center">
    <select wire:model="filterCategory" class="border rounded p-2">
        <option value="">All Categories</option>
        <option value="minor">Minor</option>
        <option value="major">Major</option>
    </select>

    <x-ui.button wire:click="exportExcel">Export Excel</x-ui.button>
    <x-ui.button wire:click="exportPDF">Export PDF</x-ui.button>

    <input type="text" wire:model.live="search" placeholder="Search..." class="border rounded p-2">
</div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full border mt-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border text-left">Name</th>
                    <th class="p-2 border text-left">Category</th>
                    <th class="p-2 border text-left">Price</th>
                    <th class="p-2 border text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->investigations as $investigation)
                    <tr>
                        <td class="p-2 border">{{ $investigation->name }}</td>
                        <td class="p-2 border capitalize">{{ $investigation->category }}</td>
                        <td class="p-2 border">{{ number_format($investigation->price, 2) }}</td>
                        <td class="p-2 border text-center space-x-2">

                            <x-ui.button
                                size="sm"
                                wire:click="edit({{ $investigation->id }})">
                                Edit
                            </x-ui.button>

                            <x-ui.button
                                size="sm"
                                variant="outline"
                                wire:click="delete({{ $investigation->id }})">
                                Delete
                            </x-ui.button>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"
                            class="p-4 text-center text-gray-500">
                            No investigations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->investigations->links() }}
    </div>


    {{-- MODAL --}}
    <x-ui.modal
        id="investigation-modal"
        :heading="$editingId ? 'Edit Investigation' : 'Add Investigation'"
        description="Fill in investigation details below."
        width="md"
    >

        {{-- TRIGGER --}}
        <x-slot:trigger>
            <x-ui.button wire:click="create">
                + Add Investigation
            </x-ui.button>
        </x-slot:trigger>

        {{-- CONTENT --}}
        <div class="space-y-4">

            <div>
                <label class="block text-sm font-medium">Name</label>
                <input type="text"
                       wire:model="name"
                       class="w-full border rounded p-2">
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Category</label>
                <select wire:model="category"
                        class="w-full border rounded p-2">
                    <option value="minor">Minor</option>
                    <option value="major">Major</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium">Price</label>
                <input type="number"
                       step="0.01"
                       wire:model="price"
                       class="w-full border rounded p-2">
                @error('price')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

        </div>

        {{-- FOOTER --}}
        <x-slot:footer>
            <x-ui.button
                variant="outline"
                x-on:click="$data.close()">
                Cancel
            </x-ui.button>

            <x-ui.button
                wire:click="save"
                wire:loading.attr="disabled">
                {{ $editingId ? 'Update' : 'Save' }}
            </x-ui.button>
        </x-slot:footer>

    </x-ui.modal>

</div>