<?php

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $company_id;

    public $name = '';

    public $email = '';

    public $phone = '';

    public $comp_logo = null;

    public $logo_preview = null;

    #[Validate('nullable|image|max:2048')]
    public $logo_upload;

    public function mount()
    {
        $this->checkAccess();

        $company = Company::findOrFail($this->company_id);
        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->logo_preview = $company->logo_url;
    }

    public function checkAccess()
    {
        abort_unless(Auth::user()->role === 'admin', 403);
    }

    public function updatedLogoUpload()
    {
        $this->validate([
            'logo_upload' => 'nullable|image|max:2048',
        ]);

        if ($this->logo_upload) {
            $this->logo_preview = $this->logo_upload->temporaryUrl();
        }
    }

    public function save()
    {
        $this->checkAccess();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'logo_upload' => 'nullable|image|max:2048',
        ]);

        $company = Company::findOrFail($this->company_id);

        if ($this->logo_upload) {
            // Delete old logo if exists
            if ($company->comp_logo && Storage::disk('public')->exists($company->comp_logo)) {
                Storage::disk('public')->delete($company->comp_logo);
            }

            // Store new logo
            $path = $this->logo_upload->store('logos', 'public');
            $company->comp_logo = $path;
        }

        $company->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        if ($this->logo_upload) {
            $company->comp_logo = $path;
            $company->save();
        }

        session()->flash('message', 'Company details updated successfully!');
        $this->dispatch('close-modal', id: 'edit-company-modal');
    }
};
?>

<x-ui.modal
    id="edit-company-modal"
    heading="Update Company Details"
    width="lg"
>

    <div class="space-y-6">

        {{-- Logo Upload --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Company Logo
            </label>

            <div class="relative">
                @if ($logo_preview)
                    <div class="mb-3 flex justify-center">
                        <img src="{{ $logo_preview }}" alt="Logo Preview" class="h-24 w-auto rounded-lg border border-gray-200 dark:border-gray-700" />
                    </div>
                @endif

                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-slate-800/50">
                    <input
                        type="file"
                        wire:model="logo_upload"
                        accept="image/*"
                        class="cursor-pointer w-full"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        PNG, JPG up to 2MB
                    </p>
                </div>

                @error('logo_upload')
                    <span class="text-red-600 dark:text-red-400 text-sm mt-2">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Name --}}
        <x-ui.field>
            <x-ui.label>Company Name</x-ui.label>
            <x-ui.input
                wire:model="name"
                placeholder="Enter company name"
            />
            @error('name')
                <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
            @enderror
        </x-ui.field>

        {{-- Email --}}
        <x-ui.field>
            <x-ui.label>Company Email</x-ui.label>
            <x-ui.input
                type="email"
                wire:model="email"
                placeholder="Enter company email"
            />
            @error('email')
                <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
            @enderror
        </x-ui.field>

        {{-- Phone --}}
        <x-ui.field>
            <x-ui.label>Company Phone</x-ui.label>
            <x-ui.input
                type="tel"
                wire:model="phone"
                placeholder="Enter company phone"
            />
            @error('phone')
                <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
            @enderror
        </x-ui.field>

    </div>

    <x-slot:footer>
        <x-ui.button
            variant="outline"
            x-on:click="$dispatch('close-modal', {id: 'edit-company-modal'})"
        >
            Cancel
        </x-ui.button>
        <x-ui.button
            wire:click="save"
            wire:loading.attr="disabled"
            wire:target="save"
        >
            <span wire:loading.remove wire:target="save">Save Changes</span>
            <span wire:loading wire:target="save">Saving...</span>
        </x-ui.button>
    </x-slot:footer>

</x-ui.modal>
