<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationFee extends Model
{
       protected $fillable = [
        'company_id',
        'patient_type',
        'amount'
    ];
}
