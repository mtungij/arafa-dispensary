<?php

use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
use WithPagination;
public $search = '';

public  getmedicinesProperty()
{
    return Medicine::where('company_id', auth()->user()->company_id)
        ->where(function($query) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('category', 'like', '%'.$this->search.'%');
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);

};
}
?>

<div class="p-6">

<x-ui.heading level="h2" size="lg">Medicine Inventory</x-ui.heading>

@if(session()->has('message'))
    <div class="bg-green-100 text-green-700 p-3 rounded mt-3">
        {{ session('message') }}
    </div>
@endif

<div class="flex justify-between items-center mt-4 mb-4">

    <input
        type="text"
        wire:model.live="search"
        placeholder="Search medicine..."
        class="border rounded p-2 w-64"
    >

    <a href="{{ route('pharmacy.medicine.create') }}">
        <x-ui.button icon="plus">Add Medicine</x-ui.button>
    </a>

</div>

<table class="w-full text-sm border">

    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">Category</th>
            <th class="p-2 text-left">Stock</th>
            <th class="p-2 text-left">Cash Price</th>
            <th class="p-2 text-left">Insurance Price</th>
            <th class="p-2 text-left">Expire Date</th>
            <th class="p-2 text-left">Action</th>
        </tr>
    </thead>

    <tbody>

    @forelse($medicines as $medicine)

        <tr class="border-t">

            <td class="p-2 font-medium">
                {{ $medicine->name }}
            </td>

            <td class="p-2">
                {{ $medicine->category }}
            </td>

            <td class="p-2">

                @if($medicine->quantity < 10)
                    <span class="text-red-600 font-semibold">
                        {{ $medicine->quantity }} (Low)
                    </span>
                @else
                    {{ $medicine->quantity }}
                @endif

            </td>

            <td class="p-2">
                {{ number_format($medicine->sell_price_cash,2) }}
            </td>

            <td class="p-2">
                {{ number_format($medicine->sell_price_insurance,2) }}
            </td>

            <td class="p-2">

                @if($medicine->expire_date && \Carbon\Carbon::parse($medicine->expire_date)->isPast())

                    <span class="text-red-600 font-semibold">
                        Expired
                    </span>

                @else

                    {{ $medicine->expire_date }}

                @endif

            </td>

            <td class="p-2 flex gap-2">

                <x-ui.button size="sm" icon="pencil">
                    Edit
                </x-ui.button>

                <x-ui.button
                    size="sm"
                    color="red"
                    icon="trash"
                    wire:click="delete({{ $medicine->id }})"
                    wire:confirm="Delete this medicine?"
                >
                    Delete
                </x-ui.button>

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="7" class="p-4 text-center text-gray-500">
                No medicines found
            </td>
        </tr>

    @endforelse

    </tbody>

</table>

<div class="mt-4">
    {{ $medicines->links() }}
</div>

</div>