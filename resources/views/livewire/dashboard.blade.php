{{-- Component Showcase Dashboard --}}
<div class="space-y-12">
    {{-- Page Header --}}
    <div>
        <x-ui.heading level="h1" size="xl">Component Showcase</x-ui.heading>
        <x-ui.text class="mt-1 opacity-60">
            A comprehensive overview of every UI component available in this starter kit.
        </x-ui.text>
    </div>
<div class="grid grid-cols-2 gap-4 mb-6">

    <!-- Cash -->
  

       <x-ui.card size="xl">
                <x-ui.heading level="h3" size="sm" class="mb-2">Cash Today Registration fee</x-ui.heading>
                  <div class="mt-2 text-3xl font-bold text-primary-content">{{ number_format($this->dailyTotals['cash'], 2) }}</div>
                <x-ui.text class="text-sm opacity-60">
                     <x-ui.text class="text-sm opacity-60">Total page views this month</x-ui.text>
            <div class="mt-3">
                     <x-ui.badge color="green" icon="arrow-trending-up" size="sm"> {{ number_format($this->dailyTotals['cashcount'], 0) }} patients</x-ui.badge>
                     </div>
                </x-ui.text>
            </x-ui.card>



              <x-ui.card size="xl">
                <x-ui.heading level="h3" size="sm" class="mb-2">Insurance Today Registration fee</x-ui.heading>
                <div class="mt-2 text-3xl font-bold text-primary-content">{{ number_format($this->dailyTotals['insurance'], 2) }}</div>
                <x-ui.text class="text-sm opacity-60">Total page views this month</x-ui.text>
                <div class="mt-3">
                    <x-ui.badge color="green" icon="arrow-trending-up" size="sm">  {{ number_format($this->dailyTotals['insurancecount'], 0) }} patients</x-ui.badge>
                </div>
            </x-ui.card>

    <!-- Insurance -->
   

    <!-- Total -->
    <div class="bg-gray-100 p-4 rounded shadow">
        <h3 class="text-lg font-bold">Total Today</h3>
        <p class="text-2xl font-semibold text-gray-800">
            {{ number_format($this->dailyTotals['total'], 2) }}
        </p>
    </div>

</div>
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- BUTTONS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="buttons" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Buttons</x-ui.heading>
        <x-ui.separator />

        {{-- Variants --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Variants</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button variant="primary">Primary</x-ui.button>
                <x-ui.button variant="solid">Solid</x-ui.button>
                <x-ui.button variant="soft">Soft</x-ui.button>
                <x-ui.button variant="outline">Outline</x-ui.button>
                <x-ui.button variant="none">None</x-ui.button>
            </div>
        </div>

        {{-- Sizes --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Sizes</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button size="xs">Extra Small</x-ui.button>
                <x-ui.button size="sm">Small</x-ui.button>
                <x-ui.button size="md">Medium</x-ui.button>
                <x-ui.button size="lg">Large</x-ui.button>
            </div>
        </div>

        {{-- Colors --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Colors</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button color="red">Red</x-ui.button>
                <x-ui.button color="blue">Blue</x-ui.button>
                <x-ui.button color="green">Green</x-ui.button>
                <x-ui.button color="amber">Amber</x-ui.button>
                <x-ui.button color="purple">Purple</x-ui.button>
                <x-ui.button color="pink">Pink</x-ui.button>
                <x-ui.button color="cyan">Cyan</x-ui.button>
            </div>
        </div>

        {{-- With Icons --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">With Icons</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button icon="plus">Create New</x-ui.button>
                <x-ui.button icon="arrow-down-tray" variant="outline">Download</x-ui.button>
                <x-ui.button iconAfter="arrow-right" variant="soft">Next Step</x-ui.button>
                <x-ui.button icon="trash" color="red" variant="soft">Delete</x-ui.button>
                <x-ui.button icon="heart" squared />
            </div>
        </div>

        {{-- States --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">States</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button disabled>Disabled</x-ui.button>
                <x-ui.button loading>Loading</x-ui.button>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- BADGES --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="badges" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Badges</x-ui.heading>
        <x-ui.separator />

        {{-- Solid Variant --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Solid</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge>Default</x-ui.badge>
                <x-ui.badge color="red">Red</x-ui.badge>
                <x-ui.badge color="green">Green</x-ui.badge>
                <x-ui.badge color="blue">Blue</x-ui.badge>
                <x-ui.badge color="amber">Amber</x-ui.badge>
                <x-ui.badge color="purple">Purple</x-ui.badge>
                <x-ui.badge color="pink">Pink</x-ui.badge>
                <x-ui.badge color="cyan">Cyan</x-ui.badge>
            </div>
        </div>

        {{-- Outline Variant --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Outline</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge variant="outline">Default</x-ui.badge>
                <x-ui.badge variant="outline" color="red">Red</x-ui.badge>
                <x-ui.badge variant="outline" color="green">Green</x-ui.badge>
                <x-ui.badge variant="outline" color="blue">Blue</x-ui.badge>
                <x-ui.badge variant="outline" color="amber">Amber</x-ui.badge>
            </div>
        </div>

        {{-- Pill Shape --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Pill</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge pill>Default Pill</x-ui.badge>
                <x-ui.badge pill color="green">Active</x-ui.badge>
                <x-ui.badge pill color="red">Inactive</x-ui.badge>
                <x-ui.badge pill variant="outline" color="blue">Pending</x-ui.badge>
            </div>
        </div>

        {{-- With Icons --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">With Icons</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge icon="check-circle" color="green">Verified</x-ui.badge>
                <x-ui.badge icon="clock" color="amber">Pending</x-ui.badge>
                <x-ui.badge icon="x-circle" color="red">Rejected</x-ui.badge>
                <x-ui.badge iconAfter="arrow-right" color="blue">View</x-ui.badge>
            </div>
        </div>

        {{-- Sizes --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Sizes</x-ui.heading>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge size="sm" color="cyan">Small</x-ui.badge>
                <x-ui.badge color="cyan">Default</x-ui.badge>
                <x-ui.badge size="lg" color="cyan">Large</x-ui.badge>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- AVATARS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="avatars" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Avatars</x-ui.heading>
        <x-ui.separator />

        {{-- Initials --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">With Initials</x-ui.heading>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.avatar name="John Doe" size="xs" />
                <x-ui.avatar name="Jane Smith" size="sm" />
                <x-ui.avatar name="Alex Johnson" size="md" />
                <x-ui.avatar name="Sarah Williams" size="lg" />
            </div>
        </div>

        {{-- Shapes --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Circle vs Square</x-ui.heading>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.avatar name="Circle" :circle="true" />
                <x-ui.avatar name="Square" :circle="false" />
            </div>
        </div>

        {{-- With Badge --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">With Status Badge</x-ui.heading>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.avatar name="Online User" badge="bg-green-500" :circle="true" />
                <x-ui.avatar name="Away User" badge="bg-amber-500" :circle="true" />
                <x-ui.avatar name="Busy User" badge="bg-red-500" :circle="true" />
                <x-ui.avatar name="Offline User" badge="bg-neutral-400" :circle="true" />
            </div>
        </div>

        {{-- With Icon Fallback --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">With Icon</x-ui.heading>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.avatar icon="user" />
                <x-ui.avatar icon="user-group" />
                <x-ui.avatar icon="building-office" />
            </div>
        </div>

        {{-- Avatar Group --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Avatar Group</x-ui.heading>
            <x-ui.avatar.group>
                <x-ui.avatar name="Alice Brown" :circle="true" size="sm" />
                <x-ui.avatar name="Bob Green" :circle="true" size="sm" />
                <x-ui.avatar name="Carol White" :circle="true" size="sm" />
                <x-ui.avatar name="Dave Black" :circle="true" size="sm" />
                <x-ui.avatar initials="+3" :circle="true" size="sm" />
            </x-ui.avatar.group>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- ALERTS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="alerts" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Alerts</x-ui.heading>
        <x-ui.separator />

        <div class="space-y-4">
            <x-ui.alerts variant="info" icon="information-circle">
                <x-ui.alerts.heading>Information</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    This is an informational alert to share useful context.
                </x-ui.alerts.description>
            </x-ui.alerts>

            <x-ui.alerts variant="success" icon="check-circle">
                <x-ui.alerts.heading>Success</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    Your changes have been saved successfully.
                </x-ui.alerts.description>
            </x-ui.alerts>

            <x-ui.alerts variant="warning" icon="exclamation-triangle">
                <x-ui.alerts.heading>Warning</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    Please review before proceeding. This action may have side effects.
                </x-ui.alerts.description>
            </x-ui.alerts>

            <x-ui.alerts variant="error" icon="x-circle">
                <x-ui.alerts.heading>Error</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    Something went wrong. Please try again or contact support.
                </x-ui.alerts.description>
            </x-ui.alerts>

            {{-- With Custom Color --}}
            <x-ui.alerts color="purple" icon="sparkles">
                <x-ui.alerts.heading>Custom Color</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    Alerts support any Tailwind color via the color prop.
                </x-ui.alerts.description>
            </x-ui.alerts>

            {{-- With Controls --}}
            <x-ui.alerts variant="info" icon="arrow-up-circle">
                <x-ui.alerts.heading>Update Available</x-ui.alerts.heading>
                <x-ui.alerts.description>
                    A new version is available. Update now for improved performance.
                </x-ui.alerts.description>
                <x-slot:controls>
                    <x-ui.button size="xs" variant="soft">Dismiss</x-ui.button>
                    <x-ui.button size="xs">Update</x-ui.button>
                </x-slot:controls>
            </x-ui.alerts>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- CARDS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="cards" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Cards</x-ui.heading>
        <x-ui.separator />

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.card size="xl">
                <x-ui.heading level="h3" size="sm" class="mb-2">Basic Card</x-ui.heading>
                <x-ui.text>
                    A simple card with heading and text content. Cards provide a contained surface for related content.
                </x-ui.text>
            </x-ui.card>

            <x-ui.card size="xl">
                <div class="flex items-center gap-3 mb-3">
                    <x-ui.avatar name="Team Project" size="sm" />
                    <div>
                        <x-ui.heading level="h3" size="sm">With Avatar</x-ui.heading>
                        <x-ui.text class="text-sm opacity-60">team@example.com</x-ui.text>
                    </div>
                </div>
                <x-ui.text>Cards compose well with avatars, badges, and other components.</x-ui.text>
                <div class="mt-3 flex gap-2">
                    <x-ui.badge color="green">Active</x-ui.badge>
                    <x-ui.badge variant="outline">Pro</x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card size="xl">
                <x-ui.heading level="h3" size="sm" class="mb-2">Stats Card</x-ui.heading>
                <div class="mt-2 text-3xl font-bold text-primary-content">2,847</div>
                <x-ui.text class="text-sm opacity-60">Total page views this month</x-ui.text>
                <div class="mt-3">
                    <x-ui.badge color="green" icon="arrow-trending-up" size="sm">+12.5%</x-ui.badge>
                </div>
            </x-ui.card>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- INPUTS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="inputs" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Inputs</x-ui.heading>
        <x-ui.separator />

        <div class="max-w-lg space-y-6">
            {{-- Basic Input --}}
            <x-ui.field>
                <x-ui.label>Basic Input</x-ui.label>
                <x-ui.input wire:model="sampleInput" placeholder="Type something..." />
            </x-ui.field>

            {{-- With Icons --}}
            <x-ui.field>
                <x-ui.label>With Prefix Icon</x-ui.label>
                <x-ui.input prefixIcon="magnifying-glass" placeholder="Search..." />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>With Suffix Icon</x-ui.label>
                <x-ui.input suffixIcon="envelope" placeholder="email@example.com" type="email" />
            </x-ui.field>

            {{-- Clearable --}}
            <x-ui.field>
                <x-ui.label>Clearable Input</x-ui.label>
                <x-ui.input clearable placeholder="Type then clear..." />
            </x-ui.field>

            {{-- Copyable --}}
            <x-ui.field>
                <x-ui.label>Copyable Input</x-ui.label>
                <x-ui.input copyable value="sk-proj-abc123xyz" />
            </x-ui.field>

            {{-- Password with Reveal --}}
            <x-ui.field>
                <x-ui.label>Password (Revealable)</x-ui.label>
                <x-ui.input type="password" revealable placeholder="Enter password..." />
            </x-ui.field>

            {{-- Disabled --}}
            <x-ui.field :disabled="true">
                <x-ui.label>Disabled Input</x-ui.label>
                <x-ui.input disabled value="Cannot edit this" />
            </x-ui.field>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- TEXTAREA --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="textarea" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Textarea</x-ui.heading>
        <x-ui.separator />

        <div class="max-w-lg space-y-6">
            <x-ui.field>
                <x-ui.label>Default Textarea</x-ui.label>
                <x-ui.textarea wire:model="sampleTextarea" placeholder="Write your message..." />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>No Resize</x-ui.label>
                <x-ui.textarea resize="none" rows="4" placeholder="Fixed height textarea..." />
            </x-ui.field>

            <x-ui.field :disabled="true">
                <x-ui.label>Disabled Textarea</x-ui.label>
                <x-ui.textarea disabled>This content cannot be edited.</x-ui.textarea>
            </x-ui.field>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- SELECT --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="select" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Select</x-ui.heading>
        <x-ui.separator />

        <div class="grid gap-6 sm:grid-cols-2">
            {{-- Basic Select (Livewire bound) --}}
            <x-ui.field>
                <x-ui.label>Basic Select</x-ui.label>
                <x-ui.select wire:model.live="selectedFruit" placeholder="Choose a fruit...">
                    <x-ui.select.option value="apple">Apple</x-ui.select.option>
                    <x-ui.select.option value="banana">Banana</x-ui.select.option>
                    <x-ui.select.option value="cherry">Cherry</x-ui.select.option>
                    <x-ui.select.option value="dragonfruit">Dragonfruit</x-ui.select.option>
                    <x-ui.select.option value="elderberry">Elderberry</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            {{-- Select with Icons --}}
            <x-ui.field>
                <x-ui.label>With Icons</x-ui.label>
                <x-ui.select
                    placeholder="Choose status..."
                    icon="flag"
                    wire:model="selectedStatus"
                >
                    <x-ui.select.option value="active" icon="check-circle">Active</x-ui.select.option>
                    <x-ui.select.option value="pending" icon="clock">Pending</x-ui.select.option>
                    <x-ui.select.option value="inactive" icon="x-circle">Inactive</x-ui.select.option>
                    <x-ui.select.option value="archived" icon="archive-box">Archived</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            {{-- Searchable Select --}}
            <x-ui.field>
                <x-ui.label>Searchable</x-ui.label>
                <x-ui.select
                    placeholder="Find a city..."
                    icon="map-pin"
                    searchable
                    wire:model="selectedCity"
                >
                    <x-ui.select.option value="nyc">New York City</x-ui.select.option>
                    <x-ui.select.option value="london">London</x-ui.select.option>
                    <x-ui.select.option value="paris">Paris</x-ui.select.option>
                    <x-ui.select.option value="tokyo">Tokyo</x-ui.select.option>
                    <x-ui.select.option value="sydney">Sydney</x-ui.select.option>
                    <x-ui.select.option value="berlin">Berlin</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            {{-- Clearable Select --}}
            <x-ui.field>
                <x-ui.label>Clearable</x-ui.label>
                <x-ui.select clearable placeholder="Select a region..." icon="globe-alt">
                    <x-ui.select.option value="na">North America</x-ui.select.option>
                    <x-ui.select.option value="eu">Europe</x-ui.select.option>
                    <x-ui.select.option value="asia">Asia Pacific</x-ui.select.option>
                    <x-ui.select.option value="sa">South America</x-ui.select.option>
                    <x-ui.select.option value="af">Africa</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            {{-- Multiple Selection --}}
            <x-ui.field>
                <x-ui.label>Multiple Selection</x-ui.label>
                <x-ui.select
                    placeholder="Choose your skills..."
                    icon="academic-cap"
                    multiple
                    clearable
                    wire:model="selectedSkills"
                >
                    <x-ui.select.option value="php" icon="code-bracket">PHP</x-ui.select.option>
                    <x-ui.select.option value="javascript" icon="bolt">JavaScript</x-ui.select.option>
                    <x-ui.select.option value="python" icon="variable">Python</x-ui.select.option>
                    <x-ui.select.option value="react" icon="cube">React</x-ui.select.option>
                    <x-ui.select.option value="vue" icon="sparkles">Vue.js</x-ui.select.option>
                    <x-ui.select.option value="laravel" icon="academic-cap">Laravel</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            {{-- Searchable + Multiple Selection --}}
            <x-ui.field>
                <x-ui.label>Searchable &amp; Multiple</x-ui.label>
                <x-ui.select
                    placeholder="Search and select members..."
                    icon="users"
                    wire:model="selectedMembers"
                    searchable
                    multiple
                    clearable
                >
                    <x-ui.select.option value="john" icon="user">John Doe</x-ui.select.option>
                    <x-ui.select.option value="jane" icon="user">Jane Smith</x-ui.select.option>
                    <x-ui.select.option value="mike" icon="user">Mike Johnson</x-ui.select.option>
                    <x-ui.select.option value="sarah" icon="user">Sarah Wilson</x-ui.select.option>
                    <x-ui.select.option value="david" icon="user">David Brown</x-ui.select.option>
                    <x-ui.select.option value="lisa" icon="user">Lisa Davis</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            {{-- Invalid State --}}
            <x-ui.field>
                <x-ui.label>Validation State</x-ui.label>
                <x-ui.select
                    placeholder="Choose option..."
                    icon="exclamation-circle"
                    invalid="true"
                    wire:model="invalidSelection"
                >
                    <x-ui.select.option value="option1">Option 1</x-ui.select.option>
                    <x-ui.select.option value="option2">Option 2</x-ui.select.option>
                    <x-ui.select.option value="option3">Option 3</x-ui.select.option>
                </x-ui.select>
                <x-ui.error>This field is required.</x-ui.error>
            </x-ui.field>

            {{-- Disabled State --}}
            <x-ui.field>
                <x-ui.label>Disabled</x-ui.label>
                <x-ui.select
                    placeholder="This is disabled..."
                    disabled
                    wire:model="disabledValue"
                >
                    <x-ui.select.option value="option1">Option 1</x-ui.select.option>
                    <x-ui.select.option value="option2">Option 2</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- SWITCH --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="switch" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Switch</x-ui.heading>
        <x-ui.separator />

        <div class="max-w-lg space-y-6">
            <x-ui.switch
                wire:model.live="switchValue"
                label="Basic Switch"
                description="Toggle this setting on or off."
            />

            <x-ui.switch
                wire:model.live="notificationsEnabled"
                label="Enable Notifications"
                description="Receive email notifications for updates."
                iconOn="bell"
                iconOff="bell-slash"
            />

            <x-ui.switch
                wire:model.live="darkModeSwitch"
                label="Dark Mode"
                description="Toggle dark mode preference."
                iconOn="moon"
                iconOff="sun"
            />

            {{-- Sizes --}}
            <x-ui.heading level="h3" size="sm" class="mb-3">Sizes</x-ui.heading>
            <div class="space-y-4">
                <x-ui.switch label="Small Switch" size="sm" />
                <x-ui.switch label="Medium Switch (default)" size="md" />
                <x-ui.switch label="Large Switch" size="lg" />
            </div>

            {{-- Alignment --}}
            <x-ui.heading level="h3" size="sm" class="mb-3">Left Aligned</x-ui.heading>
            <x-ui.switch
                label="Switch on the left"
                description="Label appears to the right of the switch."
                align="left"
            />

            {{-- Disabled --}}
            <x-ui.switch
                label="Disabled Switch"
                description="This switch cannot be toggled."
                disabled
            />
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODALS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="modals" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Modals</x-ui.heading>
        <x-ui.separator />

        <div class="flex flex-wrap gap-3">
            {{-- Basic Modal --}}
            <x-ui.modal
                id="basic-modal"
                heading="Basic Modal"
                description="This is a simple modal dialog."
                width="md"
            >
                <x-slot:trigger>
                    <x-ui.button>Open Modal</x-ui.button>
                </x-slot:trigger>

                <x-ui.text>
                    Modal content goes here. This modal includes a heading, description, and a footer with actions.
                </x-ui.text>

                <x-slot:footer>
                    <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                    <x-ui.button x-on:click="$data.close()">Confirm</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>

            {{-- Modal with Icon --}}
            <x-ui.modal
                id="icon-modal"
                heading="Delete Item?"
                description="This action cannot be undone."
                icon="exclamation-triangle"
                width="sm"
                animation="scale"
            >
                <x-slot:trigger>
                    <x-ui.button color="red" variant="soft">Danger Modal</x-ui.button>
                </x-slot:trigger>

                <x-ui.text>
                    Are you sure you want to delete this item? All associated data will be permanently removed.
                </x-ui.text>

                <x-slot:footer>
                    <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                    <x-ui.button color="red" x-on:click="$data.close()">Delete</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>

            {{-- Slideover --}}
            <x-ui.modal
                id="slideover-modal"
                heading="Slide Over Panel"
                description="This panel slides in from the right."
                slideover
                width="md"
            >
                <x-slot:trigger>
                    <x-ui.button variant="outline">Open Slideover</x-ui.button>
                </x-slot:trigger>

                <div class="space-y-4">
                    <x-ui.text>
                        Slideover modals are great for forms, detail views, or secondary navigation. They slide in from the right side of the screen.
                    </x-ui.text>
                    <x-ui.field>
                        <x-ui.label>Example Field</x-ui.label>
                        <x-ui.input placeholder="Enter something..." />
                    </x-ui.field>
                    <x-ui.field>
                        <x-ui.label>Another Field</x-ui.label>
                        <x-ui.textarea placeholder="Describe..." />
                    </x-ui.field>
                </div>

                <x-slot:footer>
                    <x-ui.button variant="outline" x-on:click="$data.close()">Cancel</x-ui.button>
                    <x-ui.button x-on:click="$data.close()">Save</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>

            {{-- Centered Modal --}}
            <x-ui.modal
                id="centered-modal"
                heading="Centered Dialog"
                position="center"
                animation="fade"
                width="sm"
            >
                <x-slot:trigger>
                    <x-ui.button variant="soft">Centered + Fade</x-ui.button>
                </x-slot:trigger>

                <x-ui.text>This modal is centered on the screen with a fade animation.</x-ui.text>

                <x-slot:footer>
                    <x-ui.button x-on:click="$data.close()">Got it</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- DROPDOWNS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="dropdowns" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Dropdowns</x-ui.heading>
        <x-ui.separator />

        <div class="flex flex-wrap gap-4">
            {{-- Basic Dropdown --}}
            <x-ui.dropdown>
                <x-slot:button>
                    <x-ui.button variant="outline" iconAfter="chevron-down">Actions</x-ui.button>
                </x-slot:button>

                <x-slot:menu class="w-48">
                    <x-ui.dropdown.item icon="pencil">Edit</x-ui.dropdown.item>
                    <x-ui.dropdown.item icon="document-duplicate">Duplicate</x-ui.dropdown.item>
                    <x-ui.dropdown.item icon="arrow-down-tray">Download</x-ui.dropdown.item>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.item icon="trash" variant="danger">Delete</x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>

            {{-- Grouped Dropdown --}}
            <x-ui.dropdown>
                <x-slot:button>
                    <x-ui.button variant="outline" iconAfter="chevron-down">Grouped</x-ui.button>
                </x-slot:button>

                <x-slot:menu class="w-56">
                    <x-ui.dropdown.group label="Navigation">
                        <x-ui.dropdown.item icon="home">Home</x-ui.dropdown.item>
                        <x-ui.dropdown.item icon="chart-bar">Analytics</x-ui.dropdown.item>
                    </x-ui.dropdown.group>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.group label="Settings">
                        <x-ui.dropdown.item icon="cog-6-tooth">Preferences</x-ui.dropdown.item>
                        <x-ui.dropdown.item icon="user">Profile</x-ui.dropdown.item>
                    </x-ui.dropdown.group>
                </x-slot:menu>
            </x-ui.dropdown>

            {{-- With Shortcuts & Submenu --}}
            <x-ui.dropdown position="bottom-start">
                <x-slot:button>
                    <x-ui.button variant="outline" iconAfter="chevron-down">Advanced</x-ui.button>
                </x-slot:button>

                <x-slot:menu class="w-56">
                    <x-ui.dropdown.item icon="clipboard" shortcut="Ctrl+C">Copy</x-ui.dropdown.item>
                    <x-ui.dropdown.item icon="clipboard-document" shortcut="Ctrl+V">Paste</x-ui.dropdown.item>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.submenu label="Share">
                        <x-ui.dropdown.item icon="envelope">Email</x-ui.dropdown.item>
                        <x-ui.dropdown.item icon="chat-bubble-left">Message</x-ui.dropdown.item>
                        <x-ui.dropdown.item icon="link">Copy Link</x-ui.dropdown.item>
                    </x-ui.dropdown.submenu>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.item icon="arrow-right-start-on-rectangle" variant="danger">
                        Sign Out
                    </x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- TOASTS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="toasts" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Toasts</x-ui.heading>
        <x-ui.separator />

        <x-ui.text class="opacity-60">Click a button to trigger a toast notification.</x-ui.text>

        <div class="flex flex-wrap gap-3">
            <x-ui.button wire:click="showSuccessToast" color="green" variant="soft" icon="check-circle">
                Success Toast
            </x-ui.button>
            <x-ui.button wire:click="showErrorToast" color="red" variant="soft" icon="x-circle">
                Error Toast
            </x-ui.button>
            <x-ui.button wire:click="showWarningToast" color="amber" variant="soft" icon="exclamation-triangle">
                Warning Toast
            </x-ui.button>
            <x-ui.button wire:click="showInfoToast" color="blue" variant="soft" icon="information-circle">
                Info Toast
            </x-ui.button>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- SEPARATORS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="separators" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Separators</x-ui.heading>
        <x-ui.separator />

        <div class="space-y-6">
            <div>
                <x-ui.heading level="h3" size="sm" class="mb-3">Horizontal (default)</x-ui.heading>
                <x-ui.separator />
            </div>

            <div>
                <x-ui.heading level="h3" size="sm" class="mb-3">With Label</x-ui.heading>
                <x-ui.separator label="OR" />
            </div>

            <div>
                <x-ui.heading level="h3" size="sm" class="mb-3">Vertical Separators</x-ui.heading>
                <div class="flex h-10 items-center gap-4">
                    <span>Section A</span>
                    <x-ui.separator vertical />
                    <span>Section B</span>
                    <x-ui.separator vertical />
                    <span>Section C</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- TABS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="tabs" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Tabs</x-ui.heading>
        <x-ui.separator />

        {{-- Outlined Variant --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Outlined (default)</x-ui.heading>
            <x-ui.tabs wire:model="selectedTab" variant="outlined">
                <x-ui.tab.group>
                    <x-ui.tab name="overview" label="Overview" icon="eye" />
                    <x-ui.tab name="analytics" label="Analytics" icon="chart-bar" />
                    <x-ui.tab name="reports" label="Reports" icon="document-chart-bar" />
                </x-ui.tab.group>

                <x-ui.tab.panel name="overview">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.heading level="h4" size="sm">Overview Panel</x-ui.heading>
                        <x-ui.text class="mt-2 opacity-60">
                            This is the overview tab content. Tabs support Livewire wire:model binding.
                        </x-ui.text>
                    </div>
                </x-ui.tab.panel>

                <x-ui.tab.panel name="analytics">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.heading level="h4" size="sm">Analytics Panel</x-ui.heading>
                        <x-ui.text class="mt-2 opacity-60">
                            View your analytics data and insights here.
                        </x-ui.text>
                    </div>
                </x-ui.tab.panel>

                <x-ui.tab.panel name="reports">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.heading level="h4" size="sm">Reports Panel</x-ui.heading>
                        <x-ui.text class="mt-2 opacity-60">
                            Generate and download reports from this panel.
                        </x-ui.text>
                    </div>
                </x-ui.tab.panel>
            </x-ui.tabs>
        </div>

        {{-- Pills Variant --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Pills Variant</x-ui.heading>
            <x-ui.tabs variant="pills" activeTab="tab-1">
                <x-ui.tab.group>
                    <x-ui.tab name="tab-1" label="All" />
                    <x-ui.tab name="tab-2" label="Active" />
                    <x-ui.tab name="tab-3" label="Archived" />
                </x-ui.tab.group>

                <x-ui.tab.panel name="tab-1">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.text>All items displayed here.</x-ui.text>
                    </div>
                </x-ui.tab.panel>
                <x-ui.tab.panel name="tab-2">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.text>Only active items.</x-ui.text>
                    </div>
                </x-ui.tab.panel>
                <x-ui.tab.panel name="tab-3">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.text>Archived items list.</x-ui.text>
                    </div>
                </x-ui.tab.panel>
            </x-ui.tabs>
        </div>

        {{-- Non-contained Variant --}}
        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Non-contained Variant</x-ui.heading>
            <x-ui.tabs variant="non-contained" activeTab="nc-1">
                <x-ui.tab.group>
                    <x-ui.tab name="nc-1" label="Details" />
                    <x-ui.tab name="nc-2" label="Activity" />
                    <x-ui.tab name="nc-3" label="Settings" />
                </x-ui.tab.group>

                <x-ui.tab.panel name="nc-1">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.text>Detail view content.</x-ui.text>
                    </div>
                </x-ui.tab.panel>
                <x-ui.tab.panel name="nc-2">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.text>Activity feed here.</x-ui.text>
                    </div>
                </x-ui.tab.panel>
                <x-ui.tab.panel name="nc-3">
                    <div class="rounded-box border border-black/5 p-6 dark:border-white/5">
                        <x-ui.text>Settings options here.</x-ui.text>
                    </div>
                </x-ui.tab.panel>
            </x-ui.tabs>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- TYPOGRAPHY --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="typography" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Typography</x-ui.heading>
        <x-ui.separator />

        {{-- Headings --}}
        <div class="space-y-3">
            <x-ui.heading level="h3" size="sm" class="mb-3 opacity-60">Headings</x-ui.heading>
            <x-ui.heading level="h1" size="xl">Heading XL (h1)</x-ui.heading>
            <x-ui.heading level="h2" size="lg">Heading LG (h2)</x-ui.heading>
            <x-ui.heading level="h3" size="md">Heading MD (h3)</x-ui.heading>
            <x-ui.heading level="h4" size="sm">Heading SM (h4)</x-ui.heading>
            <x-ui.heading level="h5" size="xs">Heading XS (h5)</x-ui.heading>
        </div>

        {{-- Text --}}
        <div class="space-y-3">
            <x-ui.heading level="h3" size="sm" class="mb-3 opacity-60">Body Text</x-ui.heading>
            <x-ui.text>
                This is the default body text component. It provides consistent typography styling across your application. Use it for paragraphs, descriptions, and general content.
            </x-ui.text>
        </div>

        {{-- Links --}}
        <div class="space-y-3">
            <x-ui.heading level="h3" size="sm" class="mb-3 opacity-60">Links</x-ui.heading>
            <div class="flex flex-wrap gap-6">
                <x-ui.link href="#">Default Link</x-ui.link>
                <x-ui.link href="#" variant="ghost">Ghost Link</x-ui.link>
                <x-ui.link href="#" variant="soft">Soft Link</x-ui.link>
                <x-ui.link href="#" :primary="false">Non-primary Link</x-ui.link>
            </div>
        </div>

        {{-- Labels & Fields --}}
        <div class="max-w-sm space-y-4">
            <x-ui.heading level="h3" size="sm" class="mb-3 opacity-60">Field, Label & Error</x-ui.heading>

            <x-ui.field :required="true">
                <x-ui.label>Required Field</x-ui.label>
                <x-ui.input placeholder="This field is required" />
                <x-ui.error :messages="['This field is required.']" />
            </x-ui.field>

            <x-ui.fieldset label="Grouped Fields">
                <x-ui.field>
                    <x-ui.label>First Name</x-ui.label>
                    <x-ui.input placeholder="John" />
                </x-ui.field>
                <x-ui.field>
                    <x-ui.label>Last Name</x-ui.label>
                    <x-ui.input placeholder="Doe" />
                </x-ui.field>
            </x-ui.fieldset>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- ICONS --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="icons" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Icons</x-ui.heading>
        <x-ui.separator />

        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Heroicons Variants</x-ui.heading>
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex flex-col items-center gap-1">
                    <x-ui.icon name="heart" variant="outline" class="size-6" />
                    <span class="text-xs opacity-50">Outline</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <x-ui.icon name="heart" variant="solid" class="size-6" />
                    <span class="text-xs opacity-50">Solid</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <x-ui.icon name="heart" variant="mini" class="size-5" />
                    <span class="text-xs opacity-50">Mini</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <x-ui.icon name="heart" variant="micro" class="size-4" />
                    <span class="text-xs opacity-50">Micro</span>
                </div>
            </div>
        </div>

        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Sample Icons</x-ui.heading>
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.icon name="home" class="size-6" />
                <x-ui.icon name="user" class="size-6" />
                <x-ui.icon name="cog-6-tooth" class="size-6" />
                <x-ui.icon name="bell" class="size-6" />
                <x-ui.icon name="envelope" class="size-6" />
                <x-ui.icon name="star" class="size-6" />
                <x-ui.icon name="bookmark" class="size-6" />
                <x-ui.icon name="lock-closed" class="size-6" />
                <x-ui.icon name="magnifying-glass" class="size-6" />
                <x-ui.icon name="arrow-path" class="size-6" />
                <x-ui.icon name="chart-bar" class="size-6" />
                <x-ui.icon name="folder" class="size-6" />
            </div>
        </div>

        <div>
            <x-ui.heading level="h3" size="sm" class="mb-3">Loading Spinner</x-ui.heading>
            <div class="flex flex-wrap items-center gap-6">
                <x-ui.icon.loading variant="outline" />
                <x-ui.icon.loading variant="solid" />
                <x-ui.icon.loading variant="mini" />
                <x-ui.icon.loading variant="micro" />
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- THEME SWITCHER --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <section id="theme" class="scroll-mt-20 space-y-6">
        <x-ui.heading level="h2" size="lg">Theme Switcher</x-ui.heading>
        <x-ui.separator />

        <div class="space-y-6">
            <div>
                <x-ui.heading level="h3" size="sm" class="mb-3">Dropdown Variant</x-ui.heading>
                <x-ui.theme-switcher variant="dropdown" />
            </div>

            <div>
                <x-ui.heading level="h3" size="sm" class="mb-3">Stacked Variant</x-ui.heading>
                <x-ui.theme-switcher variant="stacked" />
            </div>

            <div>
                <x-ui.heading level="h3" size="sm" class="mb-3">Inline Variant</x-ui.heading>
                <x-ui.theme-switcher variant="inline" />
            </div>
        </div>
    </section>
</div>