<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
new #[Layout('components.layouts.app-sidebar')] class extends Component
{
    public $name;
    public $category;
    public $quantity;
    public $buy_price;
    public $sell_price_cash;
    public $sell_price_insurance;
    public $expire_date;
   public $type = 'private';

protected function rules()
{
    return [
        'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('medicines')->where(function ($query) {
                return $query->where('company_id', auth()->user()->company_id)
                             ->where('type', $this->type);
            })
        ],
        'category' => 'nullable|string|max:255',
        'quantity' => 'required|integer|min:0',
        'buy_price' => 'required|numeric|min:0',
        'sell_price_cash' => 'nullable|numeric|min:0',
        'sell_price_insurance' => 'nullable|numeric|min:0',
        'expire_date' => 'nullable|date',
        'type' => 'required|in:insurance,private'
    ];
}

public function save()
{
    $this->validate();

    Medicine::create([
        'company_id' => auth()->user()->company_id,
        'name' => $this->name,
        'category' => $this->category,
        'quantity' => $this->quantity,
        'buy_price' => $this->buy_price,
        'sell_price_cash' => $this->type === 'private' ? $this->sell_price_cash : null,
        'sell_price_insurance' => $this->type === 'insurance' ? $this->sell_price_insurance : null,
        'expire_date' => $this->expire_date,
        'type' => $this->type
    ]);

    session()->flash('message','Medicine added successfully.');

    $this->reset([
        'name',
        'category',
        'quantity',
        'buy_price',
        'sell_price_cash',
        'sell_price_insurance',
        'expire_date'
    ]);

    $this->type = 'private';
}
};
?>

<div class="p-6 bg-gray-100 min-h-screen">

<h2 class="text-xl font-bold mb-4">Register Medicine</h2>

@if(session()->has('message'))
<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
    {{ session('message') }}
</div>
@endif

<form wire:submit.prevent="save" class="bg-white p-6 rounded shadow space-y-4">

<div>
<label class="block text-sm font-medium">Medicine Name</label>
<input type="text" wire:model="name" class="w-full border p-2 rounded">
@error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div>
<label class="block text-sm font-medium">Category</label>
<input type="text" wire:model="category" placeholder="Tablet / Syrup / Injection"
class="w-full border p-2 rounded">
</div>

<div class="grid grid-cols-3 gap-4">

<div>
<label class="block text-sm font-medium">Quantity</label>
<input type="number" wire:model="quantity" class="w-full border p-2 rounded">
</div>

<div>
<label class="block text-sm font-medium">Buy Price</label>
<input type="number" step="0.01" wire:model="buy_price" class="w-full border p-2 rounded">
</div>

<div>
<label class="block text-sm font-medium">Expire Date</label>
<input type="date" wire:model="expire_date" class="w-full border p-2 rounded">
</div>

</div>

<div>
<label class="block text-sm font-medium">Type</label>
<select wire:model.live="type" class="w-full border p-2 rounded">
<option value="private">Cash</option>
<option value="insurance">Insurance</option>
</select>
</div>

@if($type == 'private')
<div>
<label class="block text-sm font-medium">Sell Price (Cash)</label>
<input type="number" step="0.01" wire:model="sell_price_cash" class="w-full border p-2 rounded">
@error('sell_price_cash') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>
@endif

@if($type == 'insurance')
<div>
<label class="block text-sm font-medium">Sell Price (Insurance)</label>
<input type="number" step="0.01" wire:model="sell_price_insurance" class="w-full border p-2 rounded">
@error('sell_price_insurance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>
@endif

<button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">
Save Medicine
</button>

</form>

</div>