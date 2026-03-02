   
<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Support\Toast;


new  #[Layout('components.layouts.app-sidebar')] class extends Component
{

     public $users = [];
     public $departments = [];
       public $newName;
    public $email;
    public $phone;
    public $password;
    public $confirmPassword;
    public $selectedDepartment;

    public function mount()
    {
        // Multi-tenant: only users from the same company as logged-in admin
        $companyId = Auth::user()->company_id;

        // Eager load department relationship
        $this->users = User::where('company_id', $companyId)
            ->with('department') // eager load department
            ->get()
            ->toArray(); // convert to array if needed



                 $company = Auth::user()->company;

        if ($company) {
            $this->departments = $company->departments; // collection of Department models
        }
    }
    


      public function getFilteredUsersProperty(): array
    {
        if (blank($this->search)) {
            return $this->users;
        }

        $search = mb_strtolower($this->search);

        return array_values(array_filter($this->users, function (array $user) use ($search): bool {
            return str_contains(mb_strtolower($user['name']), $search)
                || str_contains(mb_strtolower($user['email']), $search)
                || str_contains(mb_strtolower($user['role']), $search);
        }));
    }


  public function delete(int $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }
if ($user->delete()) {
            session()->flash('message', 'Staff member deleted successfully.');
        } else {
            session()->flash('error', 'Failed to delete staff member. Please try again.');
            return;
        }

        // Remove from local array so UI updates
        $this->users = array_filter($this->users, fn($u) => $u['id'] !== $userId);
        $this->users = array_values($this->users);

           
    }


     public function register()
    {
        $this->validate([
            'newName' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
            'confirmPassword' => 'required|same:password',
            'selectedDepartment' => 'required|exists:departments,id',
        ]);

        $company = Auth::user()->company;

        $company->users()->create([
            'name' => $this->newName,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'role' => 'doctor', // or pharmacist/doctor etc
            'department_id' => $this->selectedDepartment,
        ]);

        // Refresh users array so table updates
        $this->users = User::where('company_id', Auth::user()->company_id)
            ->with('department')
            ->get()
            ->toArray();

        // Reset form
        $this->reset([
            'newName',
            'email',
            'phone',
            'password',
            'confirmPassword',
            'selectedDepartment',
        ]);

        session()->flash('message', 'Staff member added successfully.');
    }



    public $editName;
    public $editEmail;
    public $editPhone;
    public $editDepartment;
    public $editingIndex;

    public function openEdit(int $index): void
    {
        $user = $this->users[$index] ?? null;
        if (! $user) {
            return;
        }
        $this->editingIndex = $index;
        $this->editName = $user['name'] ?? '';
        $this->editEmail = $user['email'] ?? '';
        $this->editPhone = $user['phone'] ?? '';
        $this->editDepartment = isset($user['department']['id']) ? $user['department']['id'] : null;
    }


     public function saveEdit()
    {
        if ($this->editingIndex === null || !isset($this->users[$this->editingIndex])) {
            session()->flash('error', 'No staff member selected for editing.');
            return;
        }

        $userId = $this->users[$this->editingIndex]['id'] ?? null;
        if (!$userId) {
            session()->flash('error', 'User not found.');
            return;
        }

        $this->validate([
            'editName' => 'required|string|min:3|max:255',
            'editEmail' => 'required|email',
            'editPhone' => 'nullable|string|max:20',
            'editDepartment' => 'nullable|exists:departments,id',
        ]);

        $user = User::find($userId);
        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        $user->name = $this->editName;
        $user->email = $this->editEmail;
        $user->phone = $this->editPhone;
        $user->department_id = $this->editDepartment;
        $user->save();

        // Refresh users array so table updates
        $this->users = User::where('company_id', Auth::user()->company_id)
            ->with('department')
            ->get()
            ->toArray();

        session()->flash('message', 'Staff member updated successfully.');
    }

}
?>




<div class="space-y-6">

@if (session()->has('message'))
     <x-ui.alerts variant="success" icon="check-circle">
                <x-ui.alerts.heading>Success</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    {{ session('message') }}
                </x-ui.alerts.description>
            </x-ui.alerts>  
@endif

@if (session()->has('error'))
    <x-ui.alerts variant="error" icon="x-circle">
                <x-ui.alerts.heading>Error</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    {{ session('error') }}
                </x-ui.alerts.description>
            </x-ui.alerts>
@endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search..."
                icon="magnifying-glass"
            />
        </div>

        <x-ui.modal
            id="add-transaction-modal"
            heading="Add Staff Member"
            description="Create a new Staff Member."
            width="md"
        >
            <x-slot:trigger>
                <x-ui.button icon="plus">
                    Add Staff Member
                </x-ui.button>
            </x-slot:trigger>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>Name</x-ui.label>
                    <x-ui.input wire:model="newName" placeholder="e.g. John Doe" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Email</x-ui.label>
                    <x-ui.input wire:model="email" type="email" placeholder="e.g. john.doe@example.com" />
                </x-ui.field>


                 <x-ui.field>
                    <x-ui.label>Phone Number</x-ui.label>
                    <x-ui.input wire:model="phone" type="text" placeholder="e.g. 0712345678" />
                </x-ui.field>


                  <x-ui.field>
                <x-ui.label>Password</x-ui.label>
                <x-ui.input type="password" wire:model="password" revealable placeholder="Enter password..." />
            </x-ui.field>


              <x-ui.field>
                <x-ui.label>Confirm Password</x-ui.label>
                <x-ui.input type="password" wire:model="confirmPassword" revealable placeholder="Enter password again..." />
            </x-ui.field>

                 

                      <x-ui.field>
                <x-ui.label>Department</x-ui.label>
                <x-ui.select
                    placeholder="Find a department..."
                    icon="map-pin"
                    searchable
                    wire:model="selectedDepartment"
                >
                 @foreach($this->departments as $dept)
                    <x-ui.select.option value="{{ $dept->id }}">{{ $dept->name }}</x-ui.select.option>
                @endforeach
                </x-ui.select>
            </x-ui.field>

         
               

            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                <x-ui.button wire:click="register">Add Staff Member</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>

  
    <div class="overflow-x-auto rounded-lg border border-gray-300 dark:border-neutral-700">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-300 bg-gray-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">S/No</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Name</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Email</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Phone</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Department</th>
                    <th class="px-3 py-2 font-medium text-neutral-600 dark:text-neutral-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users as $i => $user)
                    <tr
                        class="border-b border-gray-300 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-neutral-700 dark:hover:bg-neutral-800/40"
                        wire:key="user-{{ $user['id'] }}"
                    >
                        <td class="px-3 py-2 font-medium text-neutral-900 dark:text-neutral-100">
                            <div>{{ $i + 1 }}</div>
                        </td>


                        <td class="px-3 py-2 font-medium text-neutral-900 dark:text-neutral-100">
                            <div>{{ $user['name'] }}</div>
                        </td>

                        <td class="px-3 py-2 text-neutral-500 dark:text-neutral-400">
                            {{ $user['email'] ?? '-' }}
                        </td>

                        <td class="px-3 py-2 text-neutral-500 dark:text-neutral-400">
                            {{ is_array($user['phone']) ? implode(', ', $user['phone']) : ($user['phone'] ?? '-') }}
                        </td>

                        <td class="px-3 py-2 text-neutral-500 dark:text-neutral-400">
                            {{ isset($user['department']['name']) ? $user['department']['name'] : '-' }}
                        </td>
                     
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-end gap-1">
                                {{-- View --}}
                                <x-ui.modal
                                    id="view-txn-{{ $user['id'] }}"
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
                                            <dt class="text-neutral-500 dark:text-neutral-400">Description</dt>
                                            <dd class="font-medium text-neutral-900 dark:text-neutral-100">{{$user['name'] }}</dd>
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
                                    id="delete-txn-{{$user['id'] }}"
                                    heading="Delete Transaction"
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
                                        Are you sure you want to delete <strong>{{$user['name'] }}</strong> ?
                                    </x-ui.text>

                                    <x-slot:footer>
                                        <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                                            <x-ui.button color="red" wire:click="delete({{ $user['id'] }})" x-on:click="$data.close()">Delete</x-ui.button>
                                    </x-slot:footer>
                                </x-ui.modal>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-neutral-500 dark:text-neutral-400">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Edit Staff Member Modal --}}
    <x-ui.modal
        id="edit-transaction-modal"
        heading="Edit Staff Member"
        description="Update the staff member details."
        width="md"
    >
        <div class="space-y-4">
            <x-ui.field>
                <x-ui.label>Name</x-ui.label>
                <x-ui.input wire:model="editName" placeholder="e.g. John Doe" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input wire:model="editEmail" type="email" placeholder="e.g. john.doe@example.com" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Phone Number</x-ui.label>
                <x-ui.input wire:model="editPhone" type="text" placeholder="e.g. 0712345678" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Department</x-ui.label>
                <x-ui.select
                    placeholder="Find a department..."
                    icon="map-pin"
                    searchable
                    wire:model="editDepartment"
                >
                 @foreach($this->departments as $dept)
                    <x-ui.select.option value="{{ $dept->id }}">{{ $dept->name }}</x-ui.select.option>
                @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>

        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
            <x-ui.button wire:click="saveEdit">Save Changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
