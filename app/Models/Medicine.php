<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'quantity',
        'buy_price',
        'sell_price_cash',
        'sell_price_insurance',
        'expire_date',
        'type'
    ];

    protected $casts = [
        'expire_date' => 'date',
        'buy_price' => 'decimal:2',
        'sell_price_cash' => 'decimal:2',
        'sell_price_insurance' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}