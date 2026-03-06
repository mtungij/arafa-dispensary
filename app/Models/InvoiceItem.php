<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',         // registration, consultation, lab, medicine, bed
        'description',
        'quantity',
        'unit_price',
        'total',
        'dosage',
        'frequency',
        'duration',
        'user_id',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    // Automatically calculate total
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->quantity   = $item->quantity ?? 1;
            $item->unit_price = $item->unit_price ?? 0;
            $item->total      = $item->unit_price * $item->quantity;
        });
    }
}