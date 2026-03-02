<x-slot:title>
    Verify Email
</x-slot>

<x-slot:heading>
    Verify your email
</x-slot>

<x-slot:description>
    Thanks for signing up! Please verify your email address by clicking the link we sent you.
</x-slot>

<div class="space-y-5">
    @if (session('status') == 'verification-link-sent')
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/50">
            <div class="flex items-center gap-2">
                <x-ui.icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                    A new verification link has been sent to your email.
                </p>
            </div>
        </div>
    @endif

    <p class="text-center text-sm text-neutral-500 dark:text-neutral-400">
        If you didn't receive the email, click below to request another one.
    </p>

    <x-ui.button class="w-full" wire:click="sendVerification">
        Resend verification email
    </x-ui.button>
</div>
