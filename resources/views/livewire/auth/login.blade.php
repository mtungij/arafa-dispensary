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
   <div class="fixed bottom-6 right-6 z-50 sm:bottom-8 sm:right-8 lg:w-96"> <a href="https://wa.me/255629364847?text=Hello%20HELIX%20Team%2C%20I%20would%20like%20to%20know%20more%20about%20your%20healthcare%20system." target="_blank" rel="noopener noreferrer" class="group flex items-center justify-end gap-3"> {{-- Chat Bubble (hidden on mobile, visible on larger screens) --}} <div class="hidden lg:flex lg:w-56 items-center gap-3 bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-4 border border-slate-200 dark:border-slate-800 hover:shadow-xl transition-shadow"> <div> <p class="font-semibold text-slate-900 dark:text-slate-50 text-sm"> Chat with us! </p> <p class="text-xs text-slate-600 dark:text-slate-400 mt-1"> We typically reply in minutes </p> </div> </div> {{-- WhatsApp Button --}} <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg hover:shadow-2xl group-hover:scale-110 transition-transform duration-300"> <svg class="h-7 w-7 sm:h-8 sm:w-8 text-white" fill="currentColor" viewBox="0 0 24 24"> <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.938 1.236l-.005.003-.355.214-3.681-.96.978 3.57-.23.365a9.865 9.865 0 001.514 5.963 9.88 9.88 0 008.6 4.41h.005c5.46 0 9.9-4.453 9.9-9.92 0-2.65-.54-5.168-1.595-7.591l.03-.058a9.873 9.873 0 00-1.593-2.648zm8.237 14.856H12c-4.867 0-8.8-3.97-8.8-8.855 0-1.537.37-2.998 1.025-4.287L3.54 2.723l4.559 1.194a8.845 8.845 0 014.236-1.066h.005c4.865 0 8.8 3.97 8.8 8.855 0 4.884-3.935 8.855-8.8 8.855z"/> </svg> </div> </a> </div> </div>
</form>

<x-slot:footer>
    Don't have an account?
    <a href="{{ route('register') }}" wire:navigate
       class="font-medium text-primary hover:underline">
        Create one
    </a>
</x-slot>

