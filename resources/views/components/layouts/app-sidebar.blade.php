<x-slot:title>
    {{ $title ?? config('app.name') }}
</x-slot:title>

<x-layouts.base>
    @php
        $sidebarId = 'app-sidebar';
    @endphp

@php
/** @var \App\Models\User $user */
$user = auth()->user();

// Always-available routes (system constants)
$alwaysAvailableRoutes = [
    'verification.notice',
    'app.auth.logout',
    'password.confirm',
    'verification.verify',
    'home', // add any other constant routes you want every user to access
];

// Merge DB routes + always-available routes
$userRoutes = $user->routes->pluck('name')->merge($alwaysAvailableRoutes)->unique();
@endphp
    <div
        class="grid min-h-screen transition-all duration-500"
        style="--header-height: 3.5rem;"
        x-data="{
            isMobile: window.innerWidth < 768,
            isCollapsed: window.innerWidth >= 768 && window.innerWidth < 1024,
            mobileOpen: false,

            toggle() {
                if (this.isMobile) {
                    this.mobileOpen = !this.mobileOpen;
                } else {
                    this.isCollapsed = !this.isCollapsed;
                }
            },

            closeMobile() {
                if (this.mobileOpen) {
                    this.mobileOpen = false;
                }
            },

            init() {
                const update = () => {
            [new_code]
                    this.isMobile = window.innerWidth < 768;
                    if (this.isMobile) {
                        this.isCollapsed = false;
                    }
                    if (wasMobile && !this.isMobile) {
                        this.mobileOpen = false;
                    }
                };
                window.addEventListener('resize', update);
            },

            get bodyScrollLocked() {
                return this.isMobile && this.mobileOpen;
            }
        }"
        x-effect="document.body.style.overflow = bodyScrollLocked ? 'hidden' : ''"
        :class="{
            'md:grid-cols-[4rem_1fr]': isCollapsed,
            'md:grid-cols-[16rem_1fr]': !isCollapsed && !isMobile,
            'grid-cols-[1fr]': isMobile
        }"
        :style="isMobile ? 'grid-template-areas: \'main\'' : 'grid-template-areas: \'sidebar main\''"
    >
        {{-- Mobile Backdrop --}}
        <div
            x-show="isMobile && mobileOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm"
            x-on:click="closeMobile()"
            x-cloak
        ></div>

        {{-- Sidebar --}}
        <x-ui.sidebar
            :collapsable="true"
            x-bind:data-collapsed="isCollapsed ? '' : undefined"
            x-bind:class="{
                'sticky top-0 h-screen': !isMobile,
                'w-16': isCollapsed && !isMobile,
                'w-64': !isCollapsed && !isMobile,
                'fixed inset-y-0 left-0 z-[101] w-64 shadow-2xl': isMobile,
                'translate-x-0': isMobile && mobileOpen,
                '-translate-x-full': isMobile && !mobileOpen,
                'transition-transform duration-300 ease-out': isMobile,
            }"
            x-cloak
        >
   <x-slot:brand>
    <div class="flex flex-col items-center px-2 py-3">

        <a href="{{ route('home') }}" wire:navigate class="flex flex-col items-center gap-1">

            @auth
                @php
                    /** @var \App\Models\User $user */
                    $user = auth()->user();
                    $company = $user->company ?? null;
                @endphp

                @if($company)
                    <img 
                        src="{{ $company->logo_url }}"
                        class="h-12 w-12 object-contain rounded"
                        alt="Company Logo"
                    />
                    <span class="text-lg font-bold mt-1 text-center whitespace-nowrap uppercase">{{ $company->name }}</span>
                @else
                    <span class="text-lg font-bold">{{ config('app.name') }}</span>
                @endif

            @else
                <span class="text-lg font-bold">{{ config('app.name') }}</span>
            @endauth

        </a>

    </div>
</x-slot:brand>

 <x-ui.separator class="my-2" />

            

            <x-ui.navlist>
                <x-ui.navlist.item
                    icon="home"
                    label="Dashboard"
                    :href="route('dashboard')"
                    wire:navigate
                />
              
                <x-ui.navlist.item
                    icon="cog-6-tooth"
                    label="Account"
                    :href="route('settings.account')"
                    wire:navigate
                />
            </x-ui.navlist>
<!-- 
            <x-ui.separator class="my-2" /> -->

            <x-ui.navlist>

          @if($userRoutes->intersect([
    'settings.registration-fees',
    'settings.investigation-master',
    'settings.services'
])->isNotEmpty())

<x-ui.navlist.group label="Settings" :collapsable="true">

    @if($userRoutes->contains('settings.registration-fees'))
        <x-ui.navlist.item
            icon="cog"
            label="Registration Fees"
            href="{{ route('settings.registration-fees') }}"
        />
    @endif

    @if($userRoutes->contains('settings.investigation-master'))
        <x-ui.navlist.item
            icon="identification"
            label="Investigation Master"
            href="{{ route('settings.investigation-master') }}"
        />
    @endif

    @if($userRoutes->contains('settings.services'))
        <x-ui.navlist.item
            icon="user-circle"
            label="Services"
            href="{{ route('settings.services') }}"
        />
    @endif

</x-ui.navlist.group>

@endif

  @if($userRoutes->contains('employee.index'))

<x-ui.navlist.group label="Employee Management" :collapsable="true">

    <x-ui.navlist.item 
        icon="stop" 
        label="Register Employee" 
        href="{{ route('employee.index') }}"
    />

</x-ui.navlist.group>

@endif

@if(auth()->user()->routes->contains('name','reception.index'))
<x-ui.navlist.group label="Reception" :collapsable="true">
    <x-ui.navlist.item 
        icon="bolt" 
        label="Reception Dashboard" 
        href="{{ route('reception.index') }}" 
    />
</x-ui.navlist.group>
@endif


                @if(auth()->user()->routes->contains('name','doctor.dashboard'))
<x-ui.navlist.group label="Doctor" :collapsable="true">
    <x-ui.navlist.item 
        icon="pencil-square" 
        label="Doctor Dashboard" 
        href="{{ route('doctor.dashboard') }}" 
    />
</x-ui.navlist.group>
@endif



             @if($userRoutes->contains('billing.index') || $userRoutes->contains('medicine.billing'))
<x-ui.navlist.group label="Billing & Payments" :collapsable="true">

    @if($userRoutes->contains('billing.index'))
        <x-ui.navlist.item 
            icon="cog" 
            label="Billing Dashboard" 
            href="{{ route('billing.index') }}" 
        />
    @endif

    @if($userRoutes->contains('medicine.billing'))
        <x-ui.navlist.item 
            icon="identification" 
            label="Medicine Billing" 
            href="{{ route('medicine.billing') }}" 
        />
    @endif

</x-ui.navlist.group>
@endif
                



                @if($userRoutes->contains('medicine.index'))
<x-ui.navlist.group label="Medicine" :collapsable="true">

    <x-ui.navlist.item 
        icon="cog" 
        label="Medicine" 
        href="{{ route('medicine.index') }}" 
    />

</x-ui.navlist.group>
@endif

             
@if(
    $userRoutes->contains('reports.registration-fees') ||
    $userRoutes->contains('reports.insurance-fees') ||
    $userRoutes->contains('medicine.sold')
)

<x-ui.navlist.group label="Reports" :collapsable="true">

    @if($userRoutes->contains('reports.registration-fees'))
        <x-ui.navlist.item 
            icon="minus" 
            label="Cash Registration Fees" 
            href="{{ route('reports.registration-fees') }}" 
        />
    @endif

    @if($userRoutes->contains('reports.insurance-fees'))
        <x-ui.navlist.item 
            icon="rectangle-group" 
            label="Insurance Registration Fees" 
            href="{{ route('reports.insurance-fees') }}" 
        />
    @endif

    @if($userRoutes->contains('medicine.sold'))
        <x-ui.navlist.item 
            icon="h1" 
            label="Medicine Sales" 
            href="{{ route('medicine.sold') }}" 
        />
    @endif

</x-ui.navlist.group>

@endif
            </x-ui.navlist>

            {{-- Push remaining items to bottom --}}
            <x-ui.sidebar.push />

            <x-ui.navlist>
                <x-ui.navlist.item
                    icon="arrow-right-start-on-rectangle"
                    label="Sign Out"
                    href="#"
                    x-on:click.prevent="document.getElementById('sidebar-logout-form').submit()"
                />
            </x-ui.navlist>

            <form id="sidebar-logout-form" action="{{ route('app.auth.logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </x-ui.sidebar>

        {{-- Main Content Area --}}
        <div class="flex min-h-screen flex-col [grid-area:main]">
            {{-- Top Navigation Bar --}}
            <header class="sticky top-0 z-30 flex h-14 items-center gap-4 border-b border-black/5 bg-neutral-50 px-4 dark:border-white/5 dark:bg-neutral-900 sm:px-6">
                {{-- Mobile hamburger --}}
                <button
                    x-on:click="toggle()"
                    class="inline-flex items-center justify-center rounded-field p-1.5 hover:bg-neutral-200 dark:hover:bg-white/5 md:hidden"
                >
                    <x-ui.icon name="bars-3" class="size-6" />
                    <span class="sr-only">Toggle sidebar</span>
                </button>

                {{-- Desktop sidebar toggle --}}
                <button
                    x-on:click="toggle()"
                    class="hidden items-center justify-center rounded-field p-1.5 hover:bg-neutral-200 dark:hover:bg-white/5 md:inline-flex"
                >
                    <x-ui.icon name="code-bracket-square" class="size-5" />
                    <span class="sr-only">Toggle sidebar</span>
                </button>

                <div class="flex-1"></div>

                <div class="flex items-center gap-3">
                    <x-ui.theme-switcher variant="inline" />
                    <x-ui.separator class="my-2" vertical />
                    @auth
                        <x-user-dropdown />
                    @endauth
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
