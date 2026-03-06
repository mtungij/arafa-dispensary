<div class="bg-white rounded shadow p-4">

<input
type="text"
wire:model.live="search"
placeholder="Search medicine name..."
class="w-full border rounded px-3 py-2 mb-3">

<table class="w-full text-sm">

<thead>

<tr class="border-b">
<th>Medicine</th>
<th>Category</th>
<th>Stock</th>
<th>Price</th>
<th></th>
</tr>

</thead>

<tbody>

@foreach($this->medicines as $medicine)
<tr>
    <td>{{ $medicine->name }}</td>
    <td>{{ $medicine->category ?? '-' }}</td>
    <td class="text-center">{{ $medicine->quantity }}</td> <!-- Show existing quantity -->
    <td class="text-right">{{ number_format($medicine->display_price, 2) }}</td>
    <td class="text-right">
        <button 
            wire:click="addToCart({{ $medicine->id }})" 
            class="bg-blue-600 text-white px-2 py-1 rounded"
            @if($medicine->quantity <= 0) disabled @endif
        >
            Add
        </button>
    </td>
</tr>
@endforeach

</tbody>

</table>

</div>