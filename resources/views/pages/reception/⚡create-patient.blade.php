<?php

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app-sidebar')]  class extends Component
{
    public $first_name;
    public $last_name;
    public $phone;
    public $gender;
    public $dob;

    public function save()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'gender'     => 'nullable|in:male,female',
            'dob'        => 'nullable|date',
        ]);

        // Better patient number format (MRN)
        $patientNumber = 'MRN-' . now()->format('Y') . '-' . str_pad(
            Patient::where('company_id', Auth::user()->company_id)->count() + 1,
            5,
            '0',
            STR_PAD_LEFT
        );

        $patient = Patient::create([
            'company_id'    => Auth::user()->company_id,
            'patient_number'=> $patientNumber,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'phone'         => $this->phone,
            'gender'        => $this->gender,
            'dob'           => $this->dob,
            'created_by'    => Auth::id(),
        ]);

        // Notify parent component
        $this->dispatch('patientCreated', patientId: $patient->id);

        // Reset form
        $this->reset();

        // Close modal (important)
        $this->dispatch('close-modal', id: 'create-patient-modal');
    }
}
?>

 <x-ui.modal
    id="create-patient-modal"
    heading="Register New Patient"
    description="Enter patient details below."
    width="md"
>
    <x-slot:trigger>
        <x-ui.button icon="plus">
            New Patient
        </x-ui.button>
    </x-slot:trigger>

    <div class="space-y-4">

        <x-ui.field>
            <x-ui.label>First Name</x-ui.label>
            <x-ui.input wire:model="first_name" placeholder="Enter first name" />
            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Last Name</x-ui.label>
            <x-ui.input wire:model="last_name" placeholder="Enter last name" />
            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Phone</x-ui.label>
            <x-ui.input wire:model="phone" placeholder="Enter phone number" />
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
        <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
        <x-ui.button wire:click="save">Register Patient</x-ui.button>
    </x-slot:footer>
</x-ui.modal>