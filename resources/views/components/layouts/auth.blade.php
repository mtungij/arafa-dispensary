<x-layouts.base>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-neutral-50 px-4 py-12 dark:bg-neutral-950 sm:px-6 lg:px-8">

        {{-- Ambient glow background --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-primary/5 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-[400px] w-[400px] rounded-full bg-primary/5 blur-3xl"></div>
        </div>

        {{-- Logo --}}
        <div class="relative z-10 mb-8">
            <x-app.logo />
        </div>

        {{-- Auth Card --}}
        <div class="relative z-10 w-full max-w-md">
            <div class="rounded-2xl border border-neutral-200/80 bg-white p-8 shadow-xl shadow-neutral-950/5 ring-1 ring-neutral-950/5 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-neutral-950/40 dark:ring-white/5 sm:p-10">

                {{-- Title & Description --}}
                @if (isset($heading) || isset($description))
                    <div class="mb-6 text-center">
                        @isset($heading)
                            <h2 class="text-xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {{ $heading }}
                            </h2>
                        @endisset

                        @isset($description)
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $description }}
                            </p>
                        @endisset
                    </div>
                @endif

                {{-- Form Content --}}
                {{ $slot }}
            </div>

            {{-- Footer link --}}
            @isset($footer)
                <div class="mt-6 text-center text-sm text-neutral-500 dark:text-neutral-400">
                    {{ $footer }}
                </div>
            @endisset
        </div>

        {{-- Theme Switcher --}}
        <div class="relative z-10 mt-8">
            <x-ui.theme-switcher variant="inline" />
        </div>

    </div>
</x-layouts.base>
