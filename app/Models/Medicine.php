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
}
