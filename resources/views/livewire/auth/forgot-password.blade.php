<x-slot:title>
    Forgot Password
</x-slot>

<x-slot:heading>
    Forgot your password?
</x-slot>

<x-slot:description>
    Enter your email and we'll send you a reset link
</x-slot>

@if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/50">
        <div class="flex items-center gap-2">
            <x-ui.icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
            <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                A password reset link has been sent to your email.
            </p>
        </div>
    </div>
@else
    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <x-ui.field>
            <x-ui.label>Email address</x-ui.label>
            <x-ui.input
                wire:model.live.blur="email"
                type="email"
                placeholder="you@example.com"
                autofocus
            />
            <x-ui.error name="email" />
        </x-ui.field>

        <x-ui.button class="w-full" type="submit">
            Send reset link
        </x-ui.button>
    </form>
@endif

<x-slot:footer>
    Remember your password?
    <a href="{{ route('login') }}" wire:navigate
       class="font-medium text-primary hover:underline">
        Back to sign in
    </a>
</x-slot>
