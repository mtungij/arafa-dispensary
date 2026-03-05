<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'company_id',
        'name',
        'type',
        'cash_price',
        'insurance_price',
    ];

    public function visits()
    {
        return $this->hasMany(VisitService::class);
    }
}
