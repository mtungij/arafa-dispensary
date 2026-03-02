<x-slot:title>
    Reset Password
</x-slot>

<x-slot:heading>
    Set a new password
</x-slot>

<x-slot:description>
    Choose a strong password for your account
</x-slot>

<form wire:submit="resetPassword" class="space-y-5">
    <x-ui.field>
        <x-ui.label>Email address</x-ui.label>
        <x-ui.input
            wire:model.live.blur="email"
            type="email"
            placeholder="Enter your email address"
        />
        <x-ui.error name="email" />
    </x-ui.field>

    <x-ui.field>
        <x-ui.label>New password</x-ui.label>
        <x-ui.input
            wire:model.live.blur="password"
            type="password"
            revealable
            placeholder="Enter your new password"
        />
        <x-ui.error name="password" />
    </x-ui.field>

    <x-ui.field>
        <x-ui.label>Confirm password</x-ui.label>
        <x-ui.input
            wire:model.live.blur="password_confirmation"
            type="password"
            revealable
            placeholder="Confirm your new password"
        />
        <x-ui.error name="password_confirmation" />
    </x-ui.field>

    <x-ui.button class="w-full" type="submit">
        Reset password
    </x-ui.button>
</form>

<x-slot:footer>
    Remember your password?
    <a href="{{ route('login') }}" wire:navigate
       class="font-medium text-primary hover:underline">
        Back to sign in
    </a>
</x-slot>
