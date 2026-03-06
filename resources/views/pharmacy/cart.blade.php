<div class="bg-white rounded shadow p-4">

<h3 class="font-semibold mb-3">Dispense Cart</h3>

<table class="w-full text-sm">

<thead>

<tr class="border-b">
<th>Medicine</th>
<th>Qty</th>
<th>Total</th>
</tr>

</thead>

<tbody>

@foreach($cart as $id=>$item)

<tr class="border-b">

<td>{{ $item['name'] }}</td>

<td>

<input
type="number"
wire:model.live="cart.{{ $id }}.qty"
class="w-16 border rounded px-1">

</td>

<td>

{{ number_format($item['qty'] * $item['price'],2) }}

</td>

</tr>

@endforeach

</tbody>

</table>

<hr class="my-3">

<div class="flex justify-between font-bold">

<span>Total</span>

<span>{{ number_format($this->cartTotal,2) }}</span>

</div>

<button
wire:click="sell"
class="w-full bg-green-600 text-white py-2 rounded mt-3">

Sell & Print

</button>

</div>