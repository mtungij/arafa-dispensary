<form wire:submit.prevent="register" class="space-y-6">

    {{-- Progress Indicator --}}
    <div class="flex items-center gap-2">
        <div class="h-2 flex-1 {{ $step >= 1 ? 'bg-primary' : 'bg-gray-200' }}"></div>
        <div class="h-2 flex-1 {{ $step >= 2 ? 'bg-primary' : 'bg-gray-200' }}"></div>
        <div class="h-2 flex-1 {{ $step >= 3 ? 'bg-primary' : 'bg-gray-200' }}"></div>
    </div>

    {{-- STEP 1: Company Info --}}
    @if($step === 1)
        <h3 class="text-lg font-semibold">Company Information</h3>

        <x-ui.field>
            <x-ui.label>Company Name</x-ui.label>
            <x-ui.input wire:model="form.company_name" />
            <x-ui.error name="form.company_name" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Company Email</x-ui.label>
            <x-ui.input type="email" wire:model="form.company_email" />
            <x-ui.error name="form.company_email" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Company Phone</x-ui.label>
            <x-ui.input wire:model="form.company_phone" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Company Logo</x-ui.label>
            <x-ui.input type="file" wire:model="form.comp_logo" />
            @if ($form->comp_logo)
                <img src="{{ $form->comp_logo->temporaryUrl() }}" class="h-20 w-20 object-contain mt-2 rounded" />
            @endif
        </x-ui.field>

        <x-ui.button type="button" wire:click="nextStep" class="w-full">
            Continue to Admin Setup →
        </x-ui.button>
    @endif

    {{-- STEP 2: Admin Account --}}
    @if($step === 2)
        <h3 class="text-lg font-semibold">Admin Account</h3>

        <x-ui.field>
            <x-ui.label>Name</x-ui.label>
            <x-ui.input wire:model="form.name" autofocus />
            <x-ui.error name="form.name" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Email</x-ui.label>
            <x-ui.input type="email" wire:model="form.email" />
            <x-ui.error name="form.email" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Password</x-ui.label>
            <x-ui.input type="password" wire:model="form.password" revealable />
            <x-ui.error name="form.password" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label>Confirm Password</x-ui.label>
            <x-ui.input type="password" wire:model="form.password_confirmation" revealable />
        </x-ui.field>

        <div class="flex gap-3">
            <x-ui.button type="button" wire:click="previousStep" variant="secondary">← Back</x-ui.button>
            <x-ui.button type="button" wire:click="nextStep" class="flex-1">Continue to Departments →</x-ui.button>
        </div>
    @endif
    @if($step === 3)
        <h3 class="text-lg font-semibold">Departments</h3>
        <p class="text-sm text-gray-500 mb-2">Add departments for this company. Staff will be assigned to these departments.</p>

        <div class="space-y-2">
     @foreach($form->departments as $index => $dept)
    <div class="flex gap-2 items-center">
        <x-ui.input wire:model="form.departments.{{ $index }}" placeholder="Department Name" class="flex-1"  required/>
        <x-ui.button type="button" wire:click="removeDepartment({{ $index }})" variant="danger">Remove</x-ui.button>
    </div>
@endforeach
        </div>

        <x-ui.button type="button" wire:click="addDepartment" class="mt-2">+ Add Department</x-ui.button>

        <div class="flex gap-3 mt-4">
                    <x-ui.input wire:model="form.departments.{{ $index }}" placeholder="Department Name" class="flex-1"/>
            <x-ui.button type="submit" class="flex-1">Finish & Create Company</x-ui.button>
        </div>
    @endif


</form>