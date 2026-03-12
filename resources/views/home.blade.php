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

</div>