<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        "company_id",
        "visit_id",
        "total",
        "insurance_amount",
        "patient_amount",
        "status"

        ];

         /**
     * The visit this invoice belongs to.
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * The company this invoice belongs to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The invoice items.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Recalculate totals from items.
     */
    public function recalculateTotals(): void
    {
        $total = $this->items()->sum('total');

        $insuranceAmount = $this->items()
            ->where('type', '!=', 'registration') // optional: maybe insurance doesn't cover registration
            ->sum('total') * 0.8; // example 80% coverage

        $this->total = $total;
        $this->insurance_amount = $insuranceAmount;
        $this->patient_amount = $total - $insuranceAmount;
        $this->save();
    }
public function payments()
{
    return $this->hasMany(Payment::class);
}


public function user()
{
    return $this->belongsTo(User::class);
}
    /**
     * Mark invoice as paid.
     */
   public function markAsPaid()
{
    $this->status = 'paid';
    $this->paid_at = now();
    $this->save();

    return $this; // now it returns the updated invoice
}

        
}
