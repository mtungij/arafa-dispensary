<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    
    protected $fillable = [
        'company_id',
        'patient_number',
        'first_name',
        'last_name',
        'phone',
        'gender',
        'dob',
        'address',
        'insurance_provider_id',
        'insurance_plan_id',
        'insurance_number',
        'created_by',
    ];


      public function company() {
        return $this->belongsTo(Company::class);
    }

    public function visits() {
        return $this->hasMany(Visit::class);
    }
}
