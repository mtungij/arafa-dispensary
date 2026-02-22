<x-slot:title>
    Register
</x-slot>

<x-slot:heading>
    Create your account
</x-slot>

<x-slot:description>
    Get started with a free account
</x-slot>

<form wire:submit="register" class="space-y-5">
    <x-ui.field>
        <x-ui.label>Name</x-ui.label>
        <x-ui.input
            wire:model="form.name"
            placeholder="Your full name"
            autofocus
        />
        <x-ui.error name="form.name" />
    </x-ui.field>

    <x-ui.field>
        <x-ui.label>Email address</x-ui.label>
        <x-ui.input
            wire:model="form.email"
            type="email"
            placeholder="you@example.com"
        />
        <x-ui.error name="form.email" />
    </x-ui.field>

    <x-ui.field>
        <x-ui.label>Password</x-ui.label>
        <x-ui.input
            wire:model="form.password"
            type="password"
            revealable
            placeholder="Create a secure password"
        />
        <x-ui.error name="form.password" />
    </x-ui.field>

    <x-ui.field>
        <x-ui.label>Confirm password</x-ui.label>
        <x-ui.input
            wire:model="form.password_confirmation"
            type="password"
            revealable
            placeholder="Confirm your password"
        />
        <x-ui.error name="form.password_confirmation" />
    </x-ui.field>

    <x-ui.button class="w-full" type="submit">
        Create account
    </x-ui.button>
</form>

<x-slot:footer>
    Already have an account?
    <a href="{{ route('login') }}" wire:navigate
       class="font-medium text-primary hover:underline">
        Sign in
    </a>
</x-slot>
