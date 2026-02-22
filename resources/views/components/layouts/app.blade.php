<x-slot:title>
    {{ $title ?? config('app.name') }}
</x-slot:title>

<x-layouts.base>
    <x-layouts.partials.nav />

    <div class="max-w-3xl mx-auto mt-30">
        {{ $slot }}
    </div>
</x-layouts.base>
