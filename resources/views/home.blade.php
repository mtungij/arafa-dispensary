<div class="relative min-h-screen overflow-hidden bg-neutral-50 dark:bg-neutral-950">

    {{-- Background decoration --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[800px] w-[800px] rounded-full bg-primary/5 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/4 h-[400px] w-[400px] rounded-full bg-primary/3 blur-3xl"></div>
        <div class="absolute right-0 top-1/3 h-[300px] w-[300px] rounded-full bg-primary/3 blur-3xl"></div>
    </div>

    {{-- Hero Section --}}
    <div class="relative z-10 flex min-h-screen flex-col">

        {{-- Header spacer for fixed nav --}}
        <div class="h-20"></div>

        {{-- Hero content --}}
        <div class="flex grow flex-col items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">

                {{-- Badge --}}
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white/80 px-4 py-1.5 text-sm font-medium text-neutral-600 backdrop-blur-sm dark:border-neutral-800 dark:bg-neutral-900/80 dark:text-neutral-400">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Built with Laravel, Livewire & Tailwind
                </div>

                {{-- Title --}}
                <h1 class="text-4xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 sm:text-6xl lg:text-7xl">
                    Build faster with
                    <span class="bg-gradient-to-r from-neutral-900 via-neutral-600 to-neutral-900 bg-clip-text text-transparent dark:from-neutral-100 dark:via-neutral-400 dark:to-neutral-100">
                        modern tools
                    </span>
                </h1>

                {{-- Subtitle --}}
                <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-neutral-500 dark:text-neutral-400 sm:text-xl">
                    A production-ready starter kit with authentication, responsive layouts, and 24+ UI components. Everything you need, nothing you don't.
                </p>

                {{-- CTA Buttons --}}
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    @guest
                        <x-ui.button href="{{ route('register') }}" iconAfter="arrow-right" class="px-6 py-2.5">
                            Get started free
                        </x-ui.button>
                        <x-ui.button href="{{ route('login') }}" variant="outline" class="px-6 py-2.5">
                            Sign in
                        </x-ui.button>
                    @endguest
                    @auth
                        <x-ui.button href="{{ route('dashboard') }}" iconAfter="arrow-right" class="px-6 py-2.5">
                            Go to Dashboard
                        </x-ui.button>
                    @endauth
                </div>
            </div>

            {{-- Feature cards --}}
            <div class="mx-auto mt-24 grid w-full max-w-5xl grid-cols-1 gap-6 px-4 sm:px-6 md:grid-cols-3 lg:px-8">

                {{-- Feature 1 --}}
                <div class="group rounded-2xl border border-neutral-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                        <x-ui.icon name="shield-check" class="size-5 text-neutral-600 dark:text-neutral-400" />
                    </div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-50">Authentication Ready</h3>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                        Login, register, password reset, email verification — fully tested and ready to use out of the box.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="group rounded-2xl border border-neutral-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                        <x-ui.icon name="squares-2x2" class="size-5 text-neutral-600 dark:text-neutral-400" />
                    </div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-50">24+ Components</h3>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                        Buttons, modals, selects, toasts, tabs, cards and more — beautifully crafted with dark mode support.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="group rounded-2xl border border-neutral-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                        <x-ui.icon name="code-bracket" class="size-5 text-neutral-600 dark:text-neutral-400" />
                    </div>
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-50">Fully Yours</h3>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                        Zero vendor lock-in. Every component is a Blade file you own and can customize however you like.
                    </p>
                </div>
            </div>

            {{-- Tech stack strip --}}
            <div class="mx-auto mt-20 max-w-2xl text-center">
                <p class="mb-6 text-xs font-medium uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Powered by</p>
                <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm font-medium text-neutral-400 dark:text-neutral-500">
                    <span class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                        Laravel 12
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-fuchsia-400"></span>
                        Livewire 4
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                        Tailwind CSS 4
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
                        Alpine.js
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Pest 4
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>