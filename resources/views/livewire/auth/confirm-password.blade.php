<x-slot:title>
    Confirm Password
</x-slot>

<x-slot:heading>
    Confirm your password
</x-slot>

<x-slot:description>
    Please confirm your password before continuing
</x-slot>

<form wire:submit="confirmPassword" class="space-y-5">
    <x-ui.field>
        <x-ui.label>Password</x-ui.label>
        <x-ui.input
            wire:model.live.blur="password"
            type="password"
            revealable
            placeholder="Enter your password"
            autofocus
        />
        <x-ui.error name="password" />
    </x-ui.field>

    <x-ui.button class="w-full" type="submit">
        Confirm
    </x-ui.button>
</form>
