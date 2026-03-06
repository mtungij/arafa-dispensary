<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Invoice;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;


new #[Layout('components.layouts.app-sidebar')] class extends Component
{
 
    public $invoice;
    public $search = '';
    public $cart = [];

    public function mount($invoice)
    {
        $this->invoice = Invoice::with(['visit.patient','items'])
            ->findOrFail($invoice);
    }
public function getMedicinesProperty()
{
    $query = Medicine::where('company_id', $this->invoice->company_id)
        ->where('quantity', '>', 0) // only in-stock medicines
        ->where('name', 'like', '%' . $this->search . '%');

    // Filter based on patient type
    if ($this->invoice->patient_amount > 0) {
        $query->where('sell_price_cash', '>', 0);
    } elseif ($this->invoice->insurance_amount > 0) {
        $query->where('sell_price_insurance', '>', 0);
    }

    return $query->limit(10)->get()->map(function($medicine) {
        // Set display price based on patient type
        $medicine->display_price = $this->invoice->patient_amount > 0
            ? $medicine->sell_price_cash
            : $medicine->sell_price_insurance;
        return $medicine;
    });
}

    public function getPrescriptionProperty()
{
    return $this->invoice->items
        ->where('type', 'medicine');
}

    public function updateCartQuantity($medicineId, $qty)
{
    if(isset($this->cart[$medicineId])){
        $this->cart[$medicineId]['qty'] = max(1, $qty);
    }
}

    public function getCartTotalProperty()
{
    return collect($this->cart)
        ->sum(fn($i)=>$i['price'] * $i['qty']);
}

    public function addToCart($medicineId)
    {
        $medicine = Medicine::find($medicineId);

        if(!$medicine) return;

        if(!isset($this->cart[$medicine->id])){

            $price = $this->invoice->patient_amount > 0
                ? $medicine->sell_price_cash
                : $medicine->sell_price_insurance;

            $this->cart[$medicine->id] = [
                'name'=>$medicine->name,
                'price'=>$price,
                'qty'=>1
            ];
        }
    }

  public function sell()
{
    DB::transaction(function(){

        $total = 0;

        foreach($this->cart as $id=>$item){
            $medicine = Medicine::lockForUpdate()->find($id);

            if($medicine->quantity < $item['qty']){
                throw new \Exception("Not enough stock for {$medicine->name}");
            }

            $medicine->decrement('quantity', $item['qty']);

            $total += $item['price'] * $item['qty'];

            // Create InvoiceItem
            $this->invoice->items()->create([
                'type' => 'medicine',
                'description' => $medicine->name,
                'quantity' => $item['qty'],
                'unit_price' => $item['price'],
                'total' => $item['price'] * $item['qty']
            ]);
        }

        $this->invoice->update([
            'status'=>'dispensed',
            'total' => $this->invoice->items()->sum('total')
        ]);

    });

    $this->dispatch('printReceipt', invoiceId: $this->invoice->id);

    $this->cart = [];
}
};
?>

<div class="grid grid-cols-12 gap-4">

    {{-- LEFT: Prescription --}}
    <div class="col-span-3">
        @include('pharmacy.prescription-card')
    </div>

    {{-- CENTER: Medicine Search --}}
    <div class="col-span-5">
        @include('pharmacy.medicine-search')
    </div>

    {{-- RIGHT: Dispense Cart --}}
    <div class="col-span-4">
        @include('pharmacy.cart')
    </div>

</div>