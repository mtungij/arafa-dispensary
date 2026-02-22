<x-slot:title>
    Login
</x-slot>

<x-slot:heading>
    Welcome back
</x-slot>

<x-slot:description>
    Sign in to your account to continue
</x-slot>

<form wire:submit="login" class="space-y-5">
    <x-ui.field>
        <x-ui.label>Email address</x-ui.label>
        <x-ui.input
            wire:model="form.email"
            type="email"
            placeholder="you@example.com"
            autofocus
        />
        <x-ui.error name="form.email" />
    </x-ui.field>

    <x-ui.field>
        <div class="flex items-center justify-between">
            <x-ui.label>Password</x-ui.label>
            <a href="{{ route('forgot-password') }}" wire:navigate
               class="text-xs font-medium text-primary hover:underline">
                Forgot password?
            </a>
        </div>
        <x-ui.input
            wire:model="form.password"
            type="password"
            revealable
            placeholder="Enter your password"
        />
        <x-ui.error name="form.password" />
    </x-ui.field>

    <x-ui.button class="w-full" type="submit">
        Sign in
    </x-ui.button>

    {{-- Divider --}}
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-black/10 dark:border-white/10"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white px-3 text-neutral-500 dark:bg-neutral-900 dark:text-neutral-400">or</span>
        </div>
    </div>

    {{-- Google Login --}}
    <a href="#"
       class="inline-flex w-full items-center justify-center gap-3 rounded-lg border border-black/10 bg-white px-4 py-2.5 text-sm font-medium text-neutral-700 shadow-sm transition-colors hover:bg-neutral-50 dark:border-white/10 dark:bg-white/5 dark:text-neutral-200 dark:hover:bg-white/10">
        <svg class="size-5" viewBox="-3 0 262 262" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid">
            <path d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" fill="#4285F4"/>
            <path d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" fill="#34A853"/>
            <path d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" fill="#FBBC05"/>
            <path d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" fill="#EB4335"/>
        </svg>
        Continue with Google
    </a>
</form>

<x-slot:footer>
    Don't have an account?
    <a href="{{ route('register') }}" wire:navigate
       class="font-medium text-primary hover:underline">
        Create one
    </a>
</x-slot>
