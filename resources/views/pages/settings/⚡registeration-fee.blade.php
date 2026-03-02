<?php

use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app-sidebar')] class extends Component
{
 public string $search = '';

    /** @var array<int, array<string, mixed>> */
    public array $transactions = [];

    public string $editName = '';

    public string $editAmount = '';

    public string $editCategory = '';

    public string $editDate = '';

    public int $editingIndex = -1;

    public string $editPatientType = '';

    public string $newName = '';

    public string $newAmount = '';

    public string $newCategory = 'Income';

    public string $newDate = '';
    public array $registrationFees = [];


    public $type;
    public $NewAmount;
    public  $patient_type;
    public $amount;

    public function mount(): void
    {
        $this->registrationFees = RegistrationFee::where('company_id', Auth::user()->company_id)->get()->toArray();

        // dd($this->registrationFees);
    }


   public function addRegistrationFee()
{
    $this->validate([
        'amount' => 'required|numeric|min:0',
        'patient_type' => 'required|in:cash,insurance',
    ]);

    RegistrationFee::updateOrCreate(
        [
            'company_id' => Auth::user()->company_id,
            'patient_type' => $this->patient_type,
        ],
        [
            'amount' => $this->amount,
        ]
    );

    $this->reset('amount', 'patient_type');

    $this->registrationFees = RegistrationFee::where(
        'company_id',
        Auth::user()->company_id
    )->get()->toArray();

    $this->dispatch('close-modal', id: 'add-transaction-modal');
}





   
    public function getFilteredRegistrationFeesProperty(): array
    {
        if (blank($this->search)) {
            return  $this->registrationFees;
        }

        $search = mb_strtolower($this->search);

       return array_values(array_filter($this->registrationFees, function (array $txn) use ($search): bool {
    return str_contains(mb_strtolower($txn['patient_type']), $search)
        || str_contains((string)$txn['amount'], $search);
}));
    }

    public function openEdit(int $index): void
    {
        $txn = $this->registrationFees[$index] ?? null;

        if (! $txn) {
            return;
        }

        $this->editingIndex = $index;
        $this->editPatientType = $txn['patient_type'];
        $this->editAmount = (string) $txn['amount'];
     
    }

  public function saveEdit(): void
{
    if ($this->editingIndex < 0) return;

    $txn = $this->registrationFees[$this->editingIndex];

    RegistrationFee::where('id', $txn['id'])
        ->where('company_id', Auth::user()->company_id)
        ->update([
            'patient_type' => $this->editPatientType,
            'amount' => $this->editAmount,
        ]);

    $this->registrationFees = RegistrationFee::where(
        'company_id',
        Auth::user()->company_id
    )->get()->toArray();

    $this->editingIndex = -1;

    $this->dispatch('close-modal', id: 'edit-transaction-modal');
}
 

    public function delete(int $id): void
    {
        RegistrationFee::where('id', $id)->where('company_id', Auth::user()->company_id)->delete();

        $this->registrationFees = RegistrationFee::where('company_id', Auth::user()->company_id)->get()->toArray();
    }
};
?>

<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <x-ui.heading level="h1" size="xl">Registration Fees</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            Manage your registration fees.
        </x-ui.text>
    </div>

    {{-- Toolbar: Search + Add Button --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search registration fees..."
                icon="magnifying-glass"
            />
        </div>

        <x-ui.modal
            id="add-transaction-modal"
            heading="Add Registration Fee"
            description="Create a new registration fee."
            width="md"
        >
            <x-slot:trigger>
                <x-ui.button icon="plus">
                    Add Registration Fee
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
              
                <x-ui.field>
                    <x-ui.label>Type</x-ui.label>
                    <x-ui.select wire:model="patient_type"   icon="adjustments-horizontal"    searchable placeholder="Select type">
                        <x-ui.select.option value="cash">CASH</x-ui.select.option>
                         <x-ui.select.option value="insurance">INSURANCE</x-ui.select.option>
    
                    </x-ui.select>
                </x-ui.field>

  <x-ui.field>
                <x-ui.label>With Prefix Icon</x-ui.label>
                <x-ui.input type="number" prefixIcon="magnifying-glass" wire:model="amount" placeholder="...." />
            </x-ui.field>

@error('amount') 
    <span class="text-red-500 text-sm">{{ $message }}</span> 
@enderror

            </div>

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="addRegistrationFee">Add Registration Fee</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

    {{-- Transactions Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-neutral-700">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Type</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Amount</th>
      
                    <th class="px-3 py-2 text-right font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->filteredRegistrationFees as $i => $txn)
                    <tr
                        class="border-b border-gray-300 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800/40"
                        wire:key="txn-{{ $txn['id'] }}"
                    >
                        <td class="hidden px-3 py-2 sm:table-cell">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                {{ $txn['patient_type'] }}
                            </span>
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-2 font-mono text-xs text-neutral-500 dark:text-neutral-400 md:table-cell">
                            {{ $txn['amount'] }}
                        </td>
                       
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-end gap-1">
                                {{-- View --}}
                                <x-ui.modal
                                    id="view-txn-{{ $txn['id'] }}"
                                    heading="Transaction Details"
                                    width="sm"
                                >
                                    <x-slot:trigger>
                                        <button class="rounded p-1 text-neutral-400 transition-colors hover:bg-gray-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200" title="View">
                                            <x-ui.icon name="eye" class="size-4" />
                                        </button>
                                    </x-slot:trigger>

                                    <dl class="space-y-3 text-sm">
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Type</dt>
                                            <dd class="font-medium text-neutral-900 dark:text-neutral-100">{{ $txn['patient_type'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-neutral-500 dark:text-neutral-400">Amount</dt>
                                        <dd class="font-medium text-neutral-900 dark:text-neutral-100">{{ $txn['amount'] }}</dd>
                                        </div>
                
                                    </dl>

                                    <x-slot:footer>
                                        <x-ui.button variant="outline" x-on:click="$data.close()">Close</x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>

                                {{-- Edit --}}
                                <button
                                    class="rounded p-1 text-neutral-400 transition-colors hover:bg-gray-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                                    title="Edit"
                                    wire:click="openEdit({{ $i }})"
                                    x-on:click.debounce.50ms="$nextTick(() => $modal.open('edit-transaction-modal'))"
                                >
                                    <x-ui.icon name="pencil-square" class="size-4" />
                                </button>

                                {{-- Delete --}}
                                <x-ui.modal
                                    id="delete-txn-{{ $txn['id'] }}"
                                    heading="Delete Registration Fee"
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
                                        Are you sure you want to delete <strong>{{ $txn['patient_type'] }}</strong> registration fee?
                                    </x-ui.text>

                                    <x-slot:footer>
                                        <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                                        <x-ui.button color="red" wire:click="delete({{ $txn['id'] }})" x-on:click="$data.close()">Delete</x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-neutral-500 dark:text-neutral-400">
                            No registration fees found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Edit Transaction Modal (shared) --}}
    <x-ui.modal
        id="edit-transaction-modal"
        heading="Edit Transaction"
        description="Update the transaction details."
        width="md"
    >
        <div class="space-y-4">
    
               <x-ui.field>
                    <x-ui.label>Type</x-ui.label>
                    <x-ui.select wire:model="editPatientType"   icon="adjustments-horizontal"    searchable placeholder="Select type">
                        <x-ui.select.option value="cash">CASH</x-ui.select.option>
                         <x-ui.select.option value="insurance">INSURANCE</x-ui.select.option>
    
                    </x-ui.select>
                </x-ui.field>

            <x-ui.field>
                <x-ui.label>Amount</x-ui.label>
                <x-ui.input wire:model="editAmount" type="number" step="0.01" placeholder="0.00" />
            </x-ui.field>


        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
            <x-ui.button wire:click="saveEdit">Save Changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
