<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceProvider extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'contact',
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function patients() {
        return $this->hasMany(Patient::class);
    }
}
