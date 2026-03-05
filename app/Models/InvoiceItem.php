<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
     protected $fillable = [
        'invoice_id',
        'type',         // registration, consultation, lab, medicine, bed
        'description',  // e.g., "Registration Fee", "Blood Test"
        'quantity',     // usually 1, can be more for medicines/tests
        'unit_price',   // price per unit
        'total',        // quantity * unit_price
    ];

    /**
     * Belongs to an invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function investigationRequest()
{
    return $this->hasOne(InvestigationRequest::class, 'investigation_id', 'investigation_id')
                ->where('visit_id', $this->invoice->visit_id);
}



    // Belongs to a single medicine (nullable for non-medicine items)
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Automatically calculate total when saving.
     */
   
}
