<div class="relative min-h-screen overflow-hidden bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900">

    {{-- Background decoration --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[800px] w-[800px] rounded-full bg-blue-500/5 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/4 h-[400px] w-[400px] rounded-full bg-cyan-500/3 blur-3xl"></div>
        <div class="absolute right-0 top-1/3 h-[300px] w-[300px] rounded-full bg-emerald-500/3 blur-3xl"></div>
    </div>

    <div class="relative z-10 flex min-h-screen flex-col">

        <div class="h-20"></div>

        {{-- HERO SECTION --}}
        <div class="flex grow flex-col items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">

                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-cyan-200/50 bg-cyan-50/50 px-4 py-2 text-sm font-medium text-cyan-700 backdrop-blur-sm dark:border-cyan-800/50 dark:bg-cyan-900/30 dark:text-cyan-300">
                    Complete Healthcare Management System
                </div>

                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-50 sm:text-6xl lg:text-7xl">
                    <span class="block">Welcome to</span>
                    <span class="block bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-500 bg-clip-text text-transparent">
                        HELIX
                    </span>
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600 dark:text-slate-300 sm:text-xl">
                    Streamline your healthcare operations with an integrated platform covering reception, consultations, diagnostics, pharmacy, and billing.
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    @guest
                        <x-ui.button href="{{ route('register') }}" iconAfter="arrow-right">
                            Get Started
                        </x-ui.button>
                        <x-ui.button href="{{ route('login') }}" variant="outline">
                            Sign In
                        </x-ui.button>
                    @endguest

                    @auth
                        <x-ui.button href="{{ route('dashboard') }}" iconAfter="arrow-right">
                            Go to Dashboard
                        </x-ui.button>
                    @endauth
                </div>
            </div>

            {{-- FEATURES GRID --}}
            <div class="mx-auto mt-24 grid w-full max-w-6xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

                {{-- Reception --}}
                <div class="rounded-2xl border bg-white/80 p-6 shadow-sm dark:bg-slate-900/80">
                    <h3 class="text-lg font-semibold">Reception</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Patient registration and triage management.
                    </p>
                </div>

                {{-- Doctor --}}
                <div class="rounded-2xl border bg-white/80 p-6 shadow-sm dark:bg-slate-900/80">
                    <h3 class="text-lg font-semibold">Doctor Room</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Consultation, diagnosis and prescriptions.
                    </p>
                </div>

                {{-- Laboratory --}}
                <div class="rounded-2xl border bg-white/80 p-6 shadow-sm dark:bg-slate-900/80">
                    <h3 class="text-lg font-semibold">Laboratory</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Test requests and result management.
                    </p>
                </div>

                {{-- Pharmacy --}}
                <div class="rounded-2xl border bg-white/80 p-6 shadow-sm dark:bg-slate-900/80">
                    <h3 class="text-lg font-semibold">Pharmacy</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Prescription fulfillment and medicine inventory.
                    </p>
                </div>

                {{-- Billing --}}
                <div class="rounded-2xl border bg-white/80 p-6 shadow-sm dark:bg-slate-900/80">
                    <h3 class="text-lg font-semibold">Billing</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Invoice generation and payment tracking.
                    </p>
                </div>

                {{-- Integration --}}
                <div class="rounded-2xl border bg-white/80 p-6 shadow-sm dark:bg-slate-900/80">
                    <h3 class="text-lg font-semibold">Integration Hub</h3>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Unified patient records and system integrations.
                    </p>
                </div>

            </div>
{{-- PRICING SECTION --}}
<div class="mx-auto mt-32 max-w-6xl px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50 sm:text-4xl">
            Simple & Transparent Pricing
        </h2>
        <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
            Choose the deployment option that fits your healthcare facility
        </p>
    </div>

    <div class="grid gap-8 md:grid-cols-2">

        {{-- ONLINE PLAN --}}
        <div class="relative rounded-2xl border border-blue-200 bg-white p-8 shadow-lg transition hover:shadow-xl dark:bg-slate-900 dark:border-blue-800">

            <div class="absolute right-4 top-4 rounded-full bg-blue-600 px-3 py-1 text-xs text-white">
                Popular
            </div>

            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                Online Cloud System
            </h3>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                Access HELIX from anywhere with internet
            </p>

            <div class="mt-6">
                <span class="text-4xl font-bold text-blue-600">
                    TZS 50,000
                </span>
                <span class="text-slate-500 dark:text-slate-400">
                    / month
                </span>
            </div>

            <ul class="mt-6 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                <li>✓ Cloud hosting included</li>
                <li>✓ Automatic backups</li>
                <li>✓ Access from any device</li>
                <li>✓ Continuous updates</li>
                <li>✓ Technical support</li>
            </ul>

            <a href="https://wa.me/255629364847?text=Hello%20I%20want%20HELIX%20Online"
               class="mt-8 block rounded-lg bg-blue-600 py-2.5 text-center font-medium text-white hover:bg-blue-700 transition">
               Subscribe Now
            </a>
        </div>


        {{-- OFFLINE PLAN --}}
        <div class="rounded-2xl border border-emerald-200 bg-white p-8 shadow-lg transition hover:shadow-xl dark:bg-slate-900 dark:border-emerald-800">

            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                Offline Local System
            </h3>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                Installed directly in your hospital network
            </p>

            <div class="mt-6 text-3xl font-bold text-emerald-600">
                Contact Us
            </div>

            <ul class="mt-6 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                <li>✓ Works without internet</li>
                <li>✓ Installed on hospital server</li>
                <li>✓ Custom configuration</li>
                <li>✓ Staff training included</li>
                <li>✓ One-time license option</li>
            </ul>

            <a href="https://wa.me/255629364847?text=Hello%20I%20want%20HELIX%20Offline"
               class="mt-8 block rounded-lg bg-emerald-600 py-2.5 text-center font-medium text-white hover:bg-emerald-700 transition">
               Contact Sales
            </a>
        </div>

    </div>
</div>



{{-- SYSTEM INTERFACE PREVIEW --}}
<div class="mx-auto mt-32 max-w-6xl px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50 sm:text-4xl">
            HELIX System Interface
        </h2>

        <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
            Modern dashboard designed for healthcare professionals
        </p>
    </div>

    <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-2xl dark:border-slate-800">

        <img
            src="{{ asset('images/interface1.png') }}"
            alt="HELIX System Interface"
            class="w-full object-cover">

    </div>

</div>



{{-- TESTIMONIALS --}}
<div class="mx-auto mt-32 max-w-6xl px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-50 sm:text-4xl">
            Trusted by Healthcare Providers
        </h2>

        <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
            Clinics and hospitals rely on HELIX to streamline operations
        </p>
    </div>

    <div class="grid gap-8 md:grid-cols-3">

        {{-- Testimonial 1 --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <p class="text-sm text-slate-600 dark:text-slate-400">
                “HELIX simplified our patient workflow and improved efficiency in our clinic.”
            </p>

            <div class="mt-4">
                <p class="font-semibold text-slate-900 dark:text-slate-50">
                    Dr. Michael
                </p>
                <p class="text-xs text-slate-500">
                    Medical Director
                </p>
            </div>

        </div>


        {{-- Testimonial 2 --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <p class="text-sm text-slate-600 dark:text-slate-400">
                “Managing pharmacy stock and prescriptions is now effortless.”
            </p>

            <div class="mt-4">
                <p class="font-semibold text-slate-900 dark:text-slate-50">
                    Sarah J.
                </p>
                <p class="text-xs text-slate-500">
                    Pharmacist
                </p>
            </div>

        </div>


        {{-- Testimonial 3 --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <p class="text-sm text-slate-600 dark:text-slate-400">
                “A powerful healthcare system that connects every department seamlessly.”
            </p>

            <div class="mt-4">
                <p class="font-semibold text-slate-900 dark:text-slate-50">
                    Admin Team
                </p>
                <p class="text-xs text-slate-500">
                    Private Hospital
                </p>
            </div>

        </div>

    </div>

</div>


            {{-- FINAL CTA --}}
            <div class="mx-auto mt-20 max-w-2xl text-center pb-16">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-50">
                    Ready to Transform Your Healthcare Facility?
                </h2>

                <p class="mt-4 text-slate-600 dark:text-slate-400">
                    Join healthcare providers using HELIX today.
                </p>

                <div class="mt-8 flex justify-center gap-4">
                    <x-ui.button href="{{ route('register') }}">
                        Start Free Trial
                    </x-ui.button>
                </div>
            </div>

        </div>
    </div>


    <div class="fixed bottom-6 right-6 z-50 sm:bottom-8 sm:right-8 lg:w-96"> <a href="https://wa.me/255629364847?text=Hello%20HELIX%20Team%2C%20I%20would%20like%20to%20know%20more%20about%20your%20healthcare%20system." target="_blank" rel="noopener noreferrer" class="group flex items-center justify-end gap-3"> {{-- Chat Bubble (hidden on mobile, visible on larger screens) --}} <div class="hidden lg:flex lg:w-56 items-center gap-3 bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-4 border border-slate-200 dark:border-slate-800 hover:shadow-xl transition-shadow"> <div> <p class="font-semibold text-slate-900 dark:text-slate-50 text-sm"> Chat with us! </p> <p class="text-xs text-slate-600 dark:text-slate-400 mt-1"> We typically reply in minutes </p> </div> </div> {{-- WhatsApp Button --}} <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg hover:shadow-2xl group-hover:scale-110 transition-transform duration-300"> <svg class="h-7 w-7 sm:h-8 sm:w-8 text-white" fill="currentColor" viewBox="0 0 24 24"> <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.938 1.236l-.005.003-.355.214-3.681-.96.978 3.57-.23.365a9.865 9.865 0 001.514 5.963 9.88 9.88 0 008.6 4.41h.005c5.46 0 9.9-4.453 9.9-9.92 0-2.65-.54-5.168-1.595-7.591l.03-.058a9.873 9.873 0 00-1.593-2.648zm8.237 14.856H12c-4.867 0-8.8-3.97-8.8-8.855 0-1.537.37-2.998 1.025-4.287L3.54 2.723l4.559 1.194a8.845 8.845 0 014.236-1.066h.005c4.865 0 8.8 3.97 8.8 8.855 0 4.884-3.935 8.855-8.8 8.855z"/> </svg> </div> </a> </div> </div>

</div>